<?php 
/*
	Plugin Name: Editeur_Articles
	Plugin URI: 
	Description:Editeur front end personnalisé
	Tags: post
	Author: Magniez Justine
*/



if (!defined('ABSPATH')) die();



define('USP_WP_VERSION', '4.1');
define('USP_VERSION', '20200911');
define('USP_PLUGIN', esc_html__('User Submitted Posts', 'usp'));
define('USP_PATH', plugin_basename(__FILE__));

$usp_options = get_option('usp_options');

require_once('library/core-functions.php');
require_once('library/form-functions.php');
require_once('library/enqueue-scripts.php');
require_once('library/plugin-settings.php');
require_once('library/shortcode-access.php');
require_once('library/shortcode-login.php');
require_once('library/shortcode-misc.php');
require_once('library/template-tags.php');




function usp_i18n_init() {
	
	load_plugin_textdomain('usp', false, dirname(plugin_basename(__FILE__)) .'/languages/');
	
}
add_action('plugins_loaded', 'usp_i18n_init');


	
	

function usp_require_wp_version() {
	
	$wp_version = get_bloginfo('version');
	
	if (isset($_GET['activate']) && $_GET['activate'] == 'true') {
		
		if (version_compare($wp_version, USP_WP_VERSION, '<')) {
			
			if (is_plugin_active(USP_PATH)) {
				
				deactivate_plugins(USP_PATH);
				
				$msg  = '<strong>'. USP_PLUGIN .'</strong> ';
				$msg .= esc_html__('requires WordPress ', 'usp') . USP_WP_VERSION;
				$msg .= esc_html__(' or higher, and has been deactivated! ', 'usp');
				$msg .= esc_html__('Please return to the', 'usp') .' <a href="'. admin_url() .'">';
				$msg .= esc_html__('WordPress Admin Area', 'usp') .'</a> ';
				$msg .= esc_html__('to upgrade WordPress and try again.', 'usp');
				
				wp_die($msg);
				
			}
			
		}
		
	}
	
}
add_action('admin_init', 'usp_require_wp_version');



if (!current_theme_supports('post-thumbnails')) {
	
	if (isset($usp_options['usp_featured_images']) && $usp_options['usp_featured_images']) {
		
		add_theme_support('post-thumbnails');
		
	}
	
}



if (isset($usp_options['enable_shortcodes']) && $usp_options['enable_shortcodes']) {
	
	// add_filter('the_content', 'do_shortcode', 10);
	add_filter('widget_text', 'do_shortcode', 10); 
	
}



function usp_check_required($field) {
	
	global $usp_options;
	
	if ($usp_options[$field] === 'show') return true;
	
	else return false;
	
}



function usp_get_default_title() {
	
	$time = date_i18n('Ymd', current_time('timestamp')) .'-'. date_i18n('His', current_time('timestamp'));
	
	$title = esc_html__('User Submitted Post', 'usp');
	
	$title = apply_filters('usp_default_title', $title, $time);
	
	return $title;
	
}



function usp_get_submitted_title() {
	
	global $usp_options;
	
	$option = isset($usp_options['usp_title']) ? $usp_options['usp_title'] : null;
	
	$title = usp_get_default_title();
	
	if (isset($_POST['user-submitted-title'])) $title = sanitize_text_field($_POST['user-submitted-title']);
	
	if ($option === 'optn' && empty($title)) $title = usp_get_default_title();
	
	return $title;
	
}



function usp_get_custom_field() {
	
	global $usp_options;
	
	$name = isset($usp_options['custom_name']) ? $usp_options['custom_name'] : '';
	
	$custom = isset($_POST[$name]) ? usp_sanitize_content($_POST[$name]) : '';
	
	return $custom;
	
}



function usp_get_custom_checkbox() {
	
	global $usp_options;
	
	$name = isset($usp_options['custom_checkbox_name']) ? $usp_options['custom_checkbox_name'] : '';
	
	$custom = isset($_POST[$name]) ? usp_sanitize_content($_POST[$name]) : '';
	
	return $custom;
	
}



function usp_get_submitted_category() {
	
	$category = isset($_POST['user-submitted-category']) ? $_POST['user-submitted-category'] : '';
	
	if (is_array($category)) {
		
		$cats = array();
		
		foreach ($category as $cat) $cats[] = sanitize_text_field($cat);
		
	} else {
		
		if (strpos($category, ',') !== false) {
			
			$cats = array_map('trim', explode(',', $category));
			
		} else {
			
			$cats = sanitize_text_field($category);
			
		}
		
	}
	
	return $cats;
	
}



function usp_get_submitted_tags() {
	
	$submitted_tags = isset($_POST['user-submitted-tags']) ? $_POST['user-submitted-tags'] : '';
	
	$tags = array();
	
	if (is_array($submitted_tags)) {
		
		foreach ($submitted_tags as $tag) $tags[] = sanitize_text_field($tag);
		
	} else {
		
		if (strpos($submitted_tags, ',') !== false) {
			
			$tag_array = array_map('trim', explode(',', $submitted_tags));
			
			foreach ($tag_array as $tag) $tags[] = sanitize_text_field($tag);
			
		} else {
			
			$tags[] = sanitize_text_field($submitted_tags);
			
		}
		
	}
	
	return $tags;
	
}



function usp_get_ip_address() {
	
	if (isset($_SERVER)) {
		
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
			
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip_address = $_SERVER['HTTP_CLIENT_IP'];
			
		} else {
			$ip_address = $_SERVER['REMOTE_ADDR'];
			
		}
		
	} else {
		
		if (getenv('HTTP_X_FORWARDED_FOR')) {
			$ip_address = getenv('HTTP_X_FORWARDED_FOR');
			
		} elseif (getenv('HTTP_CLIENT_IP')) {
			$ip_address = getenv('HTTP_CLIENT_IP');
			
		} else {
			$ip_address = getenv('REMOTE_ADDR');
			
		}
		
	}
	
	return sanitize_text_field($ip_address);
	
}



function usp_checkForPublicSubmission() {
	
	global $usp_options;
	
	$is_submitted = (isset($_POST['usp-nonce']) && 
	wp_verify_nonce($_POST['usp-nonce'], 'usp-nonce')) ? true : false;
	
	if ($is_submitted) {
		
		$title = usp_get_submitted_title();
		
		$ip = usp_get_ip_address();
		
		$custom = usp_get_custom_field();
		
		$checkbox = usp_get_custom_checkbox();
		
		$category = usp_get_submitted_category();
		
		$tags = usp_get_submitted_tags();
		
		$files = isset($_FILES['user-submitted-image']) ? $_FILES['user-submitted-image'] : array();
		
		$author   = isset($_POST['user-submitted-name'])     ? sanitize_text_field($_POST['user-submitted-name'])     : '';
		$url      = isset($_POST['user-submitted-url'])      ? esc_url($_POST['user-submitted-url'])                  : '';
		$email    = isset($_POST['user-submitted-email'])    ? sanitize_text_field($_POST['user-submitted-email'])    : '';
		$captcha  = isset($_POST['user-submitted-captcha'])  ? sanitize_text_field($_POST['user-submitted-captcha'])  : '';
		$verify   = isset($_POST['user-submitted-verify'])   ? sanitize_text_field($_POST['user-submitted-verify'])   : '';
		$content  = isset($_POST['user-submitted-content'])  ? usp_sanitize_content($_POST['user-submitted-content']) : '';
		
		$result = 
		usp_createPublicSubmission($title, $files, $ip, $author, $url, $email, $tags, $captcha, $verify, $content, $category, $custom, $checkbox);
		
		$post_id = false; 
		
		if (isset($result['id'])) {
			
			$post_id = $result['id'];
			
			/* Polylang plugin */
			if (function_exists('pll_set_post_language') && function_exists('pll_default_language')) {
				
				$default_or_current = 'default';
				$default_or_current = apply_filters('usp_pll_set_post_language', $default_or_current);
				
				if ($default_or_current === 'default') {
					
					pll_set_post_language($post_id, pll_default_language());
					
				} else {
					
					pll_set_post_language($post_id, pll_current_language());
					
				}
				
			}
			/* Polylang plugin */
			
		}
		
		$error = false;
		
		if (isset($result['error'])) $error = array_filter(array_unique($result['error']));
		
		if ($error) {
			
			$e = implode(',', $error);
			$e = trim($e, ',');
			
		} else {
			
			$e = 'error';
			
		}
		
		if ($post_id) {
			
			if (!empty($_POST['redirect-override'])) {
				
				$redirect = $_POST['redirect-override'];
				
				$redirect = remove_query_arg(array('usp-error'), $redirect);
				$redirect = add_query_arg(array('usp_redirect' => '1', 'success' => 1, 'post_id' => $post_id), $redirect);
				
			} else {
				
				$redirect = $_SERVER['REQUEST_URI'];
				
				$redirect = remove_query_arg(array('usp-error'), $redirect);
				$redirect = add_query_arg(array('success' => 1, 'post_id' => $post_id), $redirect);
				
			}
			
			do_action('usp_submit_success', $redirect);
			
		} else {
			
			$redirect = $_SERVER['REQUEST_URI'];
			
			$redirect = remove_query_arg(array('success', 'post_id', 'usp-error'), $redirect);
			$redirect = add_query_arg(array('usp-error' => $e), $redirect);
			
			do_action('usp_submit_error', $redirect);
			
		}
		
		wp_redirect(esc_url_raw($redirect));
		
		exit();
		
	}
	
}
add_action('parse_request', 'usp_checkForPublicSubmission', 1);



function usp_verify_recaptcha() {
	
	global $usp_options;
	
	$public  = isset($usp_options['recaptcha_public'])  ? $usp_options['recaptcha_public']  : false;
	$private = isset($usp_options['recaptcha_private']) ? $usp_options['recaptcha_private'] : false;
	$version = isset($usp_options['recaptcha_version']) ? $usp_options['recaptcha_version'] : 2;
	
	if ($version == 3) {
		
		$response = isset($_POST['recaptcha_response']) ? $_POST['recaptcha_response'] : null;
		
		$recaptcha = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='. $private .'&response='. $response);
		$recaptcha = json_decode($recaptcha);
		
		$score = apply_filters('usp_recaptcha_score', 0.5);
		
		return (($recaptcha->success == true) && ($recaptcha->score >= $score)) ? true : false;
		
	} else {
		
		if (empty($public) || empty($private)) return false;
		
		if (isset($_POST['g-recaptcha-response'])) return require_once(plugin_dir_path(__FILE__) .'recaptcha/connect.php');
		
		return false;
		
	}
	
}



function usp_sanitize_content($content) {
	
	$allowed_tags = wp_kses_allowed_html('post');
	
	$allowed_tags['style'] = array('types' => array());
	
	$allowed_tags = apply_filters('usp_content_allowed', $allowed_tags);
	
	$patterns = array('/target="_blank"/i', "/target='_blank'/i");
	
	$patterns = apply_filters('usp_content_patterns', $patterns);
	
	$replacements = array('', '');
	
	$replacements = apply_filters('usp_content_replacements', $replacements);
	
	$content = wp_kses(stripslashes($content), $allowed_tags);
	
	$content = preg_replace($patterns, $replacements, $content);
	
	return $content;
	
}



function usp_add_meta_box() {
	
	global $post;
	
	if (usp_is_public_submission()) {
		
		$screens = array('post', 'page');
		$screens = apply_filters('usp_meta_box_post_types', $screens);
		
		$name  = get_post_meta($post->ID, 'user_submit_name', true);
		$email = get_post_meta($post->ID, 'user_submit_email', true);
		$url   = get_post_meta($post->ID, 'user_submit_url', true);
		$ip    = get_post_meta($post->ID, 'user_submit_ip', true); 
		
		if (!empty($name) || !empty($email) || !empty($url) || !empty($ip)) {
			
			foreach ($screens as $screen) {
				
				add_meta_box('usp_section_id', esc_html__('User Submitted Post Info', 'usp'), 'usp_meta_box_callback', $screen, 'normal');
				
			}
			
		}
		
	}
	
}
add_action('add_meta_boxes', 'usp_add_meta_box');



function usp_meta_box_callback($post) {
	
	global $usp_options; 
	
	if (usp_is_public_submission()) {
		
		wp_nonce_field('usp_meta_box_nonce', 'usp_meta_box_nonce');
		
		$name  = get_post_meta($post->ID, 'user_submit_name', true);
		$email = get_post_meta($post->ID, 'user_submit_email', true);
		$url   = get_post_meta($post->ID, 'user_submit_url', true);
		$ip    = get_post_meta($post->ID, 'user_submit_ip', true); 
		
		if (!empty($name) || !empty($email) || !empty($url) || !empty($ip)) {
			
			echo '<ul style="margin-left:24px;list-style:square outside;">';
			
			if (!empty($name))  echo '<li>'. esc_html__('Submitter Name: ', 'usp')  . $name  .'</li>';
			if (!empty($email)) echo '<li>'. esc_html__('Submitter Email: ', 'usp') . $email .'</li>';
			if (!empty($url))   echo '<li>'. esc_html__('Submitter URL: ', 'usp')   . $url   .'</li>';
			if (!empty($ip) && !$usp_options['disable_ip_tracking']) echo '<li>'. esc_html__('Submitter IP: ', 'usp') . $ip .'</li>';
			
			echo '</ul>';
			
		}
		
	}
	
}



function usp_display_form() {
	
	global $usp_options;
	
	$default = WP_PLUGIN_DIR .'/'. basename(dirname(__FILE__)) .'/views/submission-form.php';
	
	$custom  = get_stylesheet_directory() .'/usp/submission-form.php';
	
	ob_start();
	
	if ($usp_options['usp_form_version'] === 'custom' && file_exists($custom)) include($custom);
	
	else include($default);
	
	return apply_filters('usp_form_shortcode', ob_get_clean());
	
}
add_shortcode ('user-submitted-posts', 'usp_display_form');



function user_submitted_posts() {
	
	echo usp_display_form();
	
}



function usp_outputUserSubmissionLink() {
	
	global $pagenow, $usp_options;
	
	$screen = get_current_screen();
	
	$post_type = isset($usp_options['usp_post_type']) ? $usp_options['usp_post_type'] : 'post';
	
	$current = isset($screen->post_type) ? $screen->post_type : 'post';
	
	if ($pagenow === 'edit.php' && $post_type === $current) {
		
		$link  = '<a id="usp-admin-filter" class="button" ';
		$link .= 'href="'. admin_url('edit.php?post_type='. $current .'&user_submitted=1') .'" ';
		$link .= 'title="'. esc_attr__('Show USP Posts', 'usp') .'">';
		$link .= esc_html__('USP', 'usp') .'</a>';
		
		$link = apply_filters('usp_filter_posts_link', $link, $current);
		
		echo $link;
		
	}
	
}
add_action ('restrict_manage_posts', 'usp_outputUserSubmissionLink');



function usp_addSubmittedStatusClause($wp_query) {
	
	global $pagenow;
	
	if (is_admin() && $pagenow == 'edit.php' && isset($_GET['user_submitted'])) {
		
		if ($_GET['user_submitted'] === '1') {
			
			set_query_var('meta_key', 'is_submission');
			set_query_var('meta_value', 1);
			
		} elseif ($_GET['user_submitted'] === '0') {
			
			$meta_query = array(
							'meta_query' => 
								array(
									'key' => 'is_submission',
									'compare' => 'NOT EXISTS',
									'value' => '',
								)
							);
			
			$wp_query->set('meta_query', $meta_query);
			
		}
		
	}
	
}
add_action ('parse_query', 'usp_addSubmittedStatusClause');



function usp_replaceAuthor($author) {
	
	global $post, $usp_options;
	
	$disable = isset($usp_options['disable_author']) ? $usp_options['disable_author'] : false;
	
	$isSubmission     = get_post_meta($post->ID, 'is_submission', true);
	$submissionAuthor = get_post_meta($post->ID, 'user_submit_name', true);

	if (!$disable && $isSubmission && !empty($submissionAuthor)) $author = $submissionAuthor;
	
	return apply_filters('usp_post_author', $author);
	
}
add_filter ('the_author', 'usp_replaceAuthor');



function usp_get_author($author) {
	
	global $usp_options;
	
	$error = false;
	
	$author_id = $usp_options['author'];
	
	if (!empty($author)) {
		
		if ($usp_options['usp_use_author']) {
			
			$author_info = get_user_by('login', $author);
			
			if ($author_info) {
				
				$author_id = $author_info->ID;
				
				$author = get_the_author_meta('display_name', $author_id);
				
			}
			
		}
		
	} else {
		
		if ($usp_options['usp_name'] == 'show') {
			
			$error = 'required-name';
			
		} else {
			
			$author = get_the_author_meta('display_name', $author_id);
			
		}
		
	}
	
	$author_data = array('author' => $author, 'author_id' => $author_id, 'error' => $error);
	
	return $author_data;
	
}



if (!function_exists('exif_imagetype')) {
	
	function exif_imagetype($filename) {
		
		if ((list($width, $height, $type, $attr) = getimagesize($filename)) !== false) {
			
			return $type;
			
		}
		
		return false;
		
	}
	
} 



function usp_check_images($files, $newPost) {
	
	global $usp_options;
	
	$error = array(); $file_count = 0;
	
	$name = isset($files['name'])     ? array_filter($files['name'])     : false;
	$temp = isset($files['tmp_name']) ? array_filter($files['tmp_name']) : false;
	$errr = isset($files['error'])    ? array_filter($files['error'])    : false;
	
	if ($usp_options['usp_images'] == 'show') {
		
		if (!empty($temp)) {
			
			foreach ($temp as $key => $value) if (is_uploaded_file($value)) $file_count++;
			
		}
		
		if (!empty($errr)) {
			
			foreach ($errr as $key => $value) {
				
				if (!empty($name) && $value > 0) {
						
					error_log('WP Plugin USP: File error message '. $value .'. Info @ https://bit.ly/2uTJc4D', 0);
					
					$error[] = 'file-error';
					
				}
				
			}
			
		}
		
		if ($file_count < $usp_options['min-images']) $error[] = 'file-min';
		if ($file_count > $usp_options['max-images']) $error[] = 'file-max';
		
		for ($i = 0; $i < $file_count; $i++) {
			
			$image = @getimagesize($temp[$i]);
			
			if (false === $image) {
				
				$error[] = 'file-type';
				
				break;
				
			} else {
				
				if (isset($temp[$i]) && !exif_imagetype($temp[$i])) {
					
					$error[] = 'file-type';
					
					break;
					
				}
				
				if (isset($image[0]) && !usp_width_min($image[0])) {
					
					$error[] = 'width-min';
					
					break;
					
				}
				
				if (isset($image[0]) && !usp_width_max($image[0])) {
					
					$error[] = 'width-max';
					
					break;
					
				}
				
				if (isset($image[1]) && !usp_height_min($image[1])) {
					
					$error[] = 'height-min';
					
					break;
					
				}
				
				if (isset($image[1]) && !usp_height_max($image[1])) {
					
					$error[] = 'height-max';
					
					break;
					
				}
				
				if (isset($errr[$i]) && $errr[$i] > 0) {
					
					error_log('WP Plugin USP: File error message '. $errr[$i] .'. Info @ https://bit.ly/2uTJc4D', 0);
					
					$error[] = 'file-error';
					
					break;
					
				}
				
			}
			
		}
		
	}
	
	$file_data = array('error' => $error, 'file_count' => $file_count);
	
	return $file_data;
	
}



function usp_prepare_post($title, $content, $author_id, $author, $ip) {
	
	global $usp_options;
	
	$postData = array();
	$postData['post_title']   = $title;
	$postData['post_content'] = $content;
	$postData['post_author']  = $author_id;
	$postData['post_status']  = apply_filters('usp_post_status', 'pending');
	$postData['post_name']    = sanitize_title($title);
	
	$postType = isset($usp_options['usp_post_type']) ? $usp_options['usp_post_type'] : 'post';
	
	$postData['post_type'] = apply_filters('usp_post_type', $postType);
	
	$numberApproved = $usp_options['number-approved'];
	
	if ($numberApproved == 0) {
		
		$postData['post_status'] = apply_filters('usp_post_publish', 'publish');
		
	} elseif ($numberApproved == -1) {
		
		$postData['post_status'] = apply_filters('usp_post_moderate', 'pending');
		
	} elseif ($numberApproved == -2) {
		
		$postData['post_status'] = apply_filters('usp_post_draft', 'draft');
		
	} else {
		
		$posts = get_posts(array('post_status' => 'publish', 'meta_key' => 'user_submit_name', 'meta_value' => $author));
		
		$counter = 0;
		
		foreach ($posts as $post) {
			
			$submitterName = get_post_meta($post->ID, 'user_submit_name', true);
			$submitterIp   = get_post_meta($post->ID, 'user_submit_ip', true);
			
			if ($submitterName == $author && $submitterIp == $ip) $counter++;
			
		}
		
		if ($counter >= $numberApproved) $postData['post_status'] = apply_filters('usp_post_approve', 'publish');
		
	}
	
	return apply_filters('usp_post_data', $postData);
	
}



function usp_check_duplicates($title) {
	
	global $usp_options;
	
	if ($usp_options['titles_unique']) {
		
		$check_post = get_page_by_title($title, OBJECT, 'post');
		
		if ($check_post && $check_post->ID) return false;
		
	}
	
	return true;
	
}



function usp_maybe_rotate($tmp_name, $file_local) {
	
	$image_type = function_exists('exif_imagetype') ? exif_imagetype($tmp_name) : false;
	
	if ($image_type === 2) {
		
		$image_exif = function_exists('exif_read_data') ? @exif_read_data($tmp_name) : array(); // @ cuz PHP bug
		
		if (isset($image_exif['Orientation']) && !empty($image_exif['Orientation'])) {
			
			$src = imagecreatefromjpeg($tmp_name);
			
			if ($src) {
				
				switch ($image_exif['Orientation']) {
					
					case 3:  $image = imagerotate($src, 180, 0); break;
					case 6:  $image = imagerotate($src, -90, 0); break;
					case 8:  $image = imagerotate($src,  90, 0); break;
					default: $image = null; break;
				}
				
				imagedestroy($src);
				
				if ($image) {
					
					ob_start();
					imagejpeg($image, null, 100);
					$file_local = ob_get_contents();
					ob_end_clean();
					imagedestroy($image);
					
				}
			}
			
		}
		
	}
	
	return $file_local;
	
}



function usp_random_string($length = 12) {
	
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	
	$string = substr(str_shuffle($chars), 0, $length);
	
	return $string;
	
}



function usp_unique_filename($file) {
	
	$parts = pathinfo($file); // e.g., // /www/htdocs/inc/image.jpg
	
	$dirname   = isset($parts['dirname'])   ? $parts['dirname']   : ''; // /www/htdocs/inc
	$basename  = isset($parts['basename'])  ? $parts['basename']  : ''; // image.jpg
	$extension = isset($parts['extension']) ? $parts['extension'] : ''; // jpg
	$filename  = isset($parts['filename'])  ? $parts['filename']  : ''; // image
	
	$append = '-'. usp_random_string();
	
	$file = $dirname .'/'. $filename . $append .'.'. $extension;
	
	$file = apply_filters('usp_unique_filename', $file, $dirname, $basename, $extension, $filename);
	
	return $file;
	
}



function usp_attach_images($post_id, $newPost, $files, $file_count) {
	
	global $usp_options;
	
	do_action('usp_files_before', $files);
	
	$attach_ids = array();
	
	if ($files && $file_count > 0) {
		
		usp_include_deps();
		
		for ($i = 0; $i < $file_count; $i++) {
			
			if (isset($files['tmp_name'][$i]) && !empty($files['tmp_name'][$i])) {
				
				$file_local = file_get_contents($files['tmp_name'][$i]);
				
				$tmp_name = $files['tmp_name'][$i];
				
			} else {
				
				continue;
				
			}
			
			if (isset($files['name'][$i]) && !empty($files['name'][$i])) {
				
				$append = ($file_count > 1) ? '-'. $i : '';
				
				$file_name = sanitize_file_name(basename($files['name'][$i]));
				
				$parts = pathinfo($file_name);
				
				$ext = isset($parts['extension']) ? $parts['extension'] : null;
				
				$append = apply_filters('usp_filename_append', $append, $file_name, $ext);
				
				$filename = isset($parts['filename']) ? $parts['filename'] : usp_random_string();
				
				$file_name = isset($parts['filename']) ? $parts['filename'] . $append .'.'. $ext : $file_name;
				
				$file_name = apply_filters('usp_file_name', $file_name, $filename, $append, $ext);
				
			} else {
				
				continue;
				
			}
			
			$file_local = usp_maybe_rotate($tmp_name, $file_local);
			
			$file_path = defined('USP_UPLOAD_DIR') ? USP_UPLOAD_DIR : '/';
			
			$upload_dir = apply_filters('usp_upload_directory', wp_upload_dir());
			
			$wp_filetype = wp_check_filetype($file_name, null);
			
			if (wp_mkdir_p($upload_dir['path'])) {
				
				$file = isset($upload_dir['path']) ? $upload_dir['path'] . $file_path . $file_name : null;
				$guid = isset($upload_dir['url'])  ? $upload_dir['url']  . $file_path . $file_name : null;
				
			} else {
				
				$file = isset($upload_dir['basedir']) ? $upload_dir['basedir'] . $file_path . $file_name : null;
				$guid = isset($upload_dir['baseurl']) ? $upload_dir['baseurl'] . $file_path . $file_name : null;
				
			}
			
			$file = file_exists($file) ? usp_unique_filename($file) : $file;
			
			if (stripos($ext, 'php') === false) $bytes = file_put_contents($file, $file_local);
			
			$file_type = isset($wp_filetype['type']) ? $wp_filetype['type'] : null;
			
			$params = apply_filters('wp_handle_upload', array('file' => $file, 'url' => $guid, 'type' => $file_type)); 
			
			$file      = isset($params['file']) ? $params['file'] : $file;
			$guid      = isset($params['url'])  ? $params['url']  : $guid;
			$file_type = isset($params['type']) ? $params['type'] : $file_type;
			
			$attachment = array(
				'post_mime_type' => $file_type,
				'post_name'      => $file_name,
				'post_title'     => $file_name,
				'post_status'    => 'inherit',
				'guid'           => $guid,
			);
			
			$attachment = apply_filters('usp_insert_attachment_data', $attachment);
			
			$attach_id = wp_insert_attachment($attachment, $file, $post_id);
			
			if (isset($usp_options['usp_featured_images']) && $usp_options['usp_featured_images']) {
				
				if (!has_post_thumbnail($post_id)) set_post_thumbnail($post_id, $attach_id);
				
			}
			
			$attach_data = wp_generate_attachment_metadata($attach_id, $file);
			
			wp_update_attachment_metadata($attach_id, $attach_data);
			
			if (!is_wp_error($attach_id) && wp_attachment_is_image($attach_id)) {
				
				$attach_ids[] = $attach_id;
				
				add_post_meta($post_id, 'user_submit_image', wp_get_attachment_url($attach_id));
				
			} else {
				
				wp_delete_attachment($attach_id);
				
				wp_delete_post($post_id, true);
				
				$newPost['error'][] = 'file-upload';
				
				unset($newPost['id']);
				
			}
			
		}
		
	}
	
	do_action('usp_files_after', $attach_ids);
	
	return $newPost;
	
}

add_action( 'parse_request', 'wpse132196_redirect_after_trashing_get' );
function wpse132196_redirect_after_trashing_get() {
    if ( array_key_exists( 'trashed', $_GET ) && $_GET['trashed'] == '1' ) {
        wp_redirect( home_url('/liste') );
        exit;
    }
}

function usp_createPublicSubmission($title, $files, $ip, $author, $url, $email, $tags, $captcha, $verify, $content, $category, $custom, $checkbox) {
	
	global $usp_options;
	
	// check errors
	$newPost = array('id' => false, 'error' => false);
	
	$author_data        = usp_get_author($author);
	$author             = $author_data['author'];
	$author_id          = $author_data['author_id'];
	$newPost['error'][] = $author_data['error'];
	
	$file_data          = usp_check_images($files, $newPost);
	$file_count         = $file_data['file_count'];
	$newPost['error']   = array_unique(array_merge($file_data['error'], $newPost['error']));
	
	$tags     = is_array($tags)     ? array_filter($tags)     : $tags;
	$category = is_array($category) ? array_filter($category) : $category;
	
	if (isset($usp_options['usp_title'])    && ($usp_options['usp_title']    == 'show') && empty($title))    $newPost['error'][] = 'required-title';
	if (isset($usp_options['usp_url'])      && ($usp_options['usp_url']      == 'show') && empty($url))      $newPost['error'][] = 'required-url';
	if (isset($usp_options['usp_tags'])     && ($usp_options['usp_tags']     == 'show') && empty($tags))     $newPost['error'][] = 'required-tags';
	if (isset($usp_options['usp_category']) && ($usp_options['usp_category'] == 'show') && empty($category)) $newPost['error'][] = 'required-category';
	if (isset($usp_options['usp_content'])  && ($usp_options['usp_content']  == 'show') && empty($content))  $newPost['error'][] = 'required-content';
	if (isset($usp_options['custom_field']) && ($usp_options['custom_field'] == 'show') && empty($custom))   $newPost['error'][] = 'required-custom';
	
	if (isset($usp_options['usp_recaptcha']) && ($usp_options['usp_recaptcha'] == 'show') && !usp_verify_recaptcha())     $newPost['error'][] = 'required-recaptcha';
	if (isset($usp_options['usp_captcha'])   && ($usp_options['usp_captcha']   == 'show') && !usp_spamQuestion($captcha)) $newPost['error'][] = 'required-captcha';
	
	if (isset($usp_options['usp_email']) && ($usp_options['usp_email'] == 'show')) {
		
		$email = sanitize_email($email);
		
		if (!usp_validateEmail($email)) $newPost['error'][] = 'required-email';
		
	}
	
	if (isset($usp_options['usp_email']) && ($usp_options['usp_email'] == 'optn') && !empty($email)) {
		
		$email = sanitize_email($email);
		
		if (!usp_validateEmail($email)) $newPost['error'][] = 'incorrect-email';
		
	}
	
	if (isset($usp_options['titles_unique']) && $usp_options['titles_unique'] && !usp_check_duplicates($title)) $newPost['error'][] = 'duplicate-title';
	if (!empty($verify)) $newPost['error'][] = 'spam-verify';
	
	if (isset($usp_options['custom_checkbox']) && !empty($usp_options['custom_checkbox']) && empty($checkbox)) $newPost['error'][] = 'required-checkbox';
	
	foreach ($newPost['error'] as $e) {
		
		if (!empty($e)) {
			
			unset($newPost['id']);
			
			return $newPost;
			
		}
		
	}
	
	$postData = usp_prepare_post($title, $content, $author_id, $author, $ip);
	
	$new_status = (isset($postData['post_status']) && !empty($postData['post_status'])) ? sanitize_text_field($postData['post_status']) : apply_filters('usp_post_status', 'pending');
	$postData['post_status'] = apply_filters('usp_post_status', 'pending');
	
	do_action('usp_insert_before', $postData);
	$newPost['id'] = wp_insert_post($postData);
	do_action('usp_insert_after', $newPost);
	
	$post_id = isset($newPost['id']) ? $newPost['id'] : null;
	
	if ($post_id && !is_wp_error($post_id)) {
		
		$post = get_post($post_id);
		
		$post->post_status = $new_status;
		
		wp_update_post($post);
		
		wp_set_post_tags($post_id, $tags);
		
		wp_set_post_categories($post_id, $category);
		
		$newPost = usp_attach_images($post_id, $newPost, $files, $file_count);
		
		if (isset($newPost['error'][0]) && empty($newPost['error'][0])) {
			
			update_post_meta($post_id, 'is_submission', true);
			update_post_meta($post_id, 'usp-post-id', $post_id);
			//fonction ajout d image mise en avant 
	if (!function_exists('wp_generate_attachment_metadata')){
						require_once(ABSPATH . "wp-admin" . '/includes/image.php');
						require_once(ABSPATH . "wp-admin" . '/includes/file.php');
						require_once(ABSPATH . "wp-admin" . '/includes/media.php');
					}
					if ($_FILES) {
						foreach ($_FILES as $file => $array) {
							if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
								return "upload error : " . $_FILES[$file]['error'];
							}
							$attach_id = media_handle_upload( $file, $post_id );
						}   
					}
					if ($attach_id > 0){
						//and if you want to set that image as Post  then use:
						update_post_meta($post_id,'_thumbnail_id',$attach_id);
					}
			//
			$custom_name   = isset($usp_options['custom_name'])          ? $usp_options['custom_name']          : 'usp_custom_field';
			$checkbox_name = isset($usp_options['custom_checkbox_name']) ? $usp_options['custom_checkbox_name'] : 'usp_custom_checkbox';
			
			if (!empty($custom))   update_post_meta($post_id, $custom_name,        $custom);
			if (!empty($checkbox)) update_post_meta($post_id, $checkbox_name,      $checkbox);
			if (!empty($author))   update_post_meta($post_id, 'user_submit_name',  $author);
			if (!empty($email))    update_post_meta($post_id, 'user_submit_email', $email);
			if (!empty($url))      update_post_meta($post_id, 'user_submit_url',   $url);
			
			if (!empty($ip) && !$usp_options['disable_ip_tracking']) update_post_meta($post_id, 'user_submit_ip', $ip); 
			
			usp_send_mail_alert($post_id, $title, $content, $author, $email, $url, $custom);
			
		}
		
	} else {
		
		$newPost['error'][] = 'post-fail';
		
	}
	
	

	return apply_filters('usp_new_post', $newPost);

}




function usp_include_deps() {
	
	if (!function_exists('media_handle_upload')) {
		
		require_once (ABSPATH .'/wp-admin/includes/media.php');
		require_once (ABSPATH .'/wp-admin/includes/file.php');
		require_once (ABSPATH .'/wp-admin/includes/image.php');
		
	}
	
}



function usp_width_min($width) {
	
	global $usp_options;
	
	if (intval($width) < intval($usp_options['min-image-width'])) return false;
	
	else return true;
	
}



function usp_width_max($width) {
	
	global $usp_options;
	
	if (intval($width) > intval($usp_options['max-image-width'])) return false;
	
	else return true;
	
}



function usp_height_min($height) {
	
	global $usp_options;
	
	if (intval($height) < intval($usp_options['min-image-height'])) return false;
	
	else return true;
	
}



function usp_height_max($height) {
	
	global $usp_options;
	
	if (intval($height) > intval($usp_options['max-image-height'])) return false;
	
	else return true;
	
}



function usp_validateEmail($email) {
	
	if (!is_email($email)) return false;
	
	$bad_stuff = array("\r", "\n", "mime-version", "content-type", "cc:", "to:");
	
	foreach ($bad_stuff as $bad) {
		
		if (strpos(strtolower($email), strtolower($bad)) !== false) {
			
			return false;
			
		}
		
	}
	
	return true;
	
}

function usp_send_mail_alert($post_id, $title, $content, $author, $email, $url, $custom) {
	
	global $usp_options;
	
	if (isset($usp_options['usp_email_alerts']) && $usp_options['usp_email_alerts']) {
		
		$blog_url     = get_bloginfo('url');        // %%blog_url%%
		$blog_name    = get_bloginfo('name');       // %%blog_name%%
		$post_url     = get_permalink($post_id);    // %%post_url%%
		$admin_url    = admin_url();                // %%admin_url%%
		$post_title   = $title;                     // %%post_title%%
		$post_content = $content;                   // %%post_content%%
		$post_author  = $author;                    // %%post_author%%
		$user_email   = $email;                     // %%user_email%%
		$user_url     = $url;                       // %%user_url%%
		$edit_link    = admin_url('post.php?post='. $post_id .'&action=edit'); // %%edit_link%%
		
		$patterns = array();
		
		$patterns[0]  = "/%%blog_url%%/";
		$patterns[1]  = "/%%blog_name%%/";
		$patterns[2]  = "/%%post_url%%/";
		$patterns[3]  = "/%%admin_url%%/";
		$patterns[4]  = "/%%post_title%%/";
		$patterns[5]  = "/%%post_content%%/";
		$patterns[6]  = "/%%post_author%%/";
		$patterns[7]  = "/%%user_email%%/";
		$patterns[8]  = "/%%user_url%%/";
		$patterns[9]  = "/%%edit_link%%/";
		$patterns[10] = "/%%custom_field%%/";
		
		$replacements = array();
		
		$replacements[0]  = $blog_url;
		$replacements[1]  = $blog_name;
		$replacements[2]  = $post_url;
		$replacements[3]  = $admin_url;
		$replacements[4]  = $post_title;
		$replacements[5]  = $post_content;
		$replacements[6]  = $post_author;
		$replacements[7]  = $user_email;
		$replacements[8]  = $user_url;
		$replacements[9]  = $edit_link;
		$replacements[10] = $custom;
		
		//
		
		$subject_default = $blog_name .': New user-submitted post!';
		$subject = (isset($usp_options['email_alert_subject']) && !empty($usp_options['email_alert_subject'])) ? $usp_options['email_alert_subject'] : $subject_default;
		$subject = preg_replace($patterns, $replacements, $subject);
		$subject = apply_filters('usp_mail_subject', $subject);
		
		$message_default = 'Hello, there is a new user-submitted post:'. "\r\n\n" . 'Title: '. $post_title . "\r\n\n" .'Visit Admin Area: '. $admin_url;
		$message = (isset($usp_options['email_alert_message']) && !empty($usp_options['email_alert_message'])) ? $usp_options['email_alert_message'] : $message_default;
		$message = preg_replace($patterns, $replacements, $message);
		$message = apply_filters('usp_mail_message', $message);
		
		$html = isset($usp_options['usp_email_html']) ? $usp_options['usp_email_html'] : false;
		$format = $html ? 'text/html' : 'text/plain';
		
		//
		
		$default = get_bloginfo('admin_email');
		
		$to   = (isset($usp_options['usp_email_address']) && !empty($usp_options['usp_email_address'])) ? $usp_options['usp_email_address'] : $default;
		$from = (isset($usp_options['usp_email_from'])    && !empty($usp_options['usp_email_from']))    ? $usp_options['usp_email_from']    : $to;
		
		$to   = explode(',', $to);
		$from = explode(',', $from);
		
		$address = array();
		
		foreach ($to   as $k => $v) $address[$k]['to']   = trim($v);
		foreach ($from as $k => $v) $address[$k]['from'] = trim($v);
		
		if (!empty($address[0])) {
			
			foreach ($address as $k => $v) {
				
				$address_to   = (isset($v['to'])   && !empty($v['to']))   ? $v['to']   : $default;
				$address_from = (isset($v['from']) && !empty($v['from'])) ? $v['from'] : $default;
				
				$headers  = 'X-Mailer: User Submitted Posts'. "\n";
				$headers .= 'From: '. $blog_name .' <'. $address_from .'>'. "\n";
				$headers .= 'Reply-To: '. $blog_name .' <'. $address_from .'>'. "\n";
				$headers .= 'Content-Type: '. $format .'; charset='. get_option('blog_charset', 'UTF-8') . "\n";
				
				wp_mail($address_to, $subject, $message, $headers);
				
			}
			
		}
		
	}
	
}



function usp_spamQuestion($input) {
	
	global $usp_options;
	
	$response = $usp_options['usp_response'];
	
	$response = sanitize_text_field($response);
	
	if ($usp_options['usp_casing'] == false) {
		
		return (strtoupper($input) == strtoupper($response));
		
	} else {
		
		return ($input == $response);
		
	}
	
}



function usp_error_message() {
	
	global $usp_options;
	
	$min = $usp_options['min-images'];
	$max = $usp_options['max-images'];
	
	if ((int) $min > 1) $min = ' ('. $min . esc_html__(' files required', 'usp') .')';
	else $min = ' ('. $min . esc_html__(' file required', 'usp') .')';
	
	if ((int) $max > 1) $max = ' (limit: '. $max . esc_html__(' files', 'usp') .')';
	else $max = ' (limit: '. $max . esc_html__(' file', 'usp') .')';
	
	$min_width  = ' ('. $usp_options['min-image-width']  . esc_html__(' pixels', 'usp') .')';
	$max_width  = ' ('. $usp_options['max-image-width']  . esc_html__(' pixels', 'usp') .')';
	$min_height = ' ('. $usp_options['min-image-height'] . esc_html__(' pixels', 'usp') .')';
	$max_height = ' ('. $usp_options['max-image-height'] . esc_html__(' pixels', 'usp') .')';
	
	$custom_label = isset($usp_options['custom_label']) ? $usp_options['custom_label'] : __('Custom Field', 'usp');
	
	$checkbox_label = isset($usp_options['custom_checkbox_err']) ? $usp_options['custom_checkbox_err'] : __('Custom checkbox required', 'usp');
	
	if (!empty($usp_options['error-message'])) $general_error = $usp_options['error-message'];
	else $general_error = esc_html__('An error occurred. Please go back and try again.', 'usp');
	
	if (isset($_GET['usp-error']) && !empty($_GET['usp-error'])) {
		
		$error_string = sanitize_text_field($_GET['usp-error']);
		$error_array = explode(',', $error_string);
		$error = array();
		
		foreach ($error_array as $e) {
			
			if      ($e == 'required-name')       $error[] = esc_html__('User name required', 'usp');
			elseif ($e == 'required-title')      $error[] = esc_html__('Post title required', 'usp');
			
			elseif ($e == 'required-tags')       $error[] = esc_html__('Post tags required', 'usp');
			elseif ($e == 'required-category')   $error[] = esc_html__('Post category required', 'usp');
			elseif ($e == 'required-content')    $error[] = esc_html__('Post content required', 'usp');
			elseif ($e == 'file-type')           $error[] = esc_html__('File type not allowed (please upload images only)', 'usp');
			elseif ($e == 'required-custom')     $error[] = esc_html($custom_label) . esc_html__(' required', 'usp');
			elseif ($e == 'required-checkbox')   $error[] = esc_html($checkbox_label);
			
			// general error for file uploads, check error log for description.
			// check server for proper values of memory_limit, max_execution_time, max_input_time, post_max_size, upload_max_filesize
			elseif ($e == 'file-error')          $error[] = esc_html__('File not uploaded. Please check the file and try again.', 'usp');
			
			// check permissions on /uploads/ directory, check error log for the following error:
			// PHP Warning: mysql_real_escape_string() expects parameter 1 to be string, object given in /wp-includes/wp-db.php
			elseif ($e == 'file-upload')         $error[] = esc_html__('The file(s) could not be uploaded', 'usp'); 
			
			elseif ($e == 'post-fail')           $error[] = esc_html__('Post not created. Please contact the site administrator for help.', 'usp');
			elseif ($e == 'duplicate-title')     $error[] = esc_html__('Duplicate post title. Please try again.', 'usp');
			
			elseif ($e == 'error')               $error[] = $general_error;
			
		}
		
		$output = '';
		
		foreach ($error as $e) {
			
			$output .= "\t\t\t".'<div class="usp-error">'. esc_html__('Error: ', 'usp') . $e .'</div>'."\n";
			
		}
		
		$return = '<div id="usp-error-message">'."\n". $output ."\t\t".'</div>'."\n";
		
		return apply_filters('usp_error_message', $return);
		
	}
	
	return false;
	
}



function usp_redirect_message($content = '') {
	
	global $usp_options;
	
	$url = (isset($usp_options['redirect-url']) && !empty($usp_options['redirect-url'])) ? true : false;
	
	$enable = (!is_admin() && (isset($_GET['usp_redirect']) && $_GET['usp_redirect'] == '1')) ? true : false;
	
	$referrer = (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) ? esc_url($_SERVER['HTTP_REFERER']) : false;
	
	$link = $referrer ? '<p id="usp-return-form"><a href="'. $referrer .'">'. esc_html__('Return to form', 'usp') .'</a></p>' : '';
	
	$link = apply_filters('usp_return_form', $link, $referrer);
	
	$message = '';
	
	if ($url && $enable) {
		
		if (isset($_GET['success']) && $_GET['success'] == '1') {
			
			$message = '<p id="usp-success-message"><strong>'. $usp_options['success-message'] .'</strong></p>'. $link;
			
		} else {
			
			$message = usp_error_message() . $link;
			
		}
		
	}
	
	return $message . $content;
	
}



function usp_login_required_message() {
	
	$url = apply_filters('usp_require_login_url', wp_login_url());
	
	$message  = '<p>'. esc_html__('Please', 'usp');
	$message .= ' <a href="'. esc_url($url) .'">'. esc_html__('log in', 'usp') .'</a> ';
	$message .= esc_html__('to submit content!', 'usp') .'</p>';
	
	$message = apply_filters('usp_require_login', $message);
	
	return $message;
	
}



function usp_clear_cookies() {
	
	$cookies = array(
		'user-submitted-name',
		'user-submitted-email',
		'user-submitted-url',
		'user-submitted-title',
		'user-submitted-tags',
		'user-submitted-category',
		'user-submitted-content',
		'user-submitted-custom',
		'user-submitted-checkbox',
		'user-submitted-captcha'
	);
	
	foreach ($cookies as $cookie) {
		
		if (isset($_COOKIE[$cookie]) && !empty($_COOKIE[$cookie])) {
			
			unset($_COOKIE[$cookie]);
			setcookie($cookie, null, time() - 3600, '/');
			
		}
		
	}
	
}
add_action('wp_logout', 'usp_clear_cookies');

