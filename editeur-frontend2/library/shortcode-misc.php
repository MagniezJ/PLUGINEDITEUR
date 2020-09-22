<?php // User Submitted Posts - Shortcodes misc.

/*
	Shortcode: Reset form button
	Returns the markup for a reset-form button
	Syntax: [usp-reset-button class="aaa,bbb,ccc" value="Reset form" url="https://example.com/usp-pro/submit/"]
	Attributes:
		class  = classes for the parent element (optional, default: none)
		value  = link text (optional, default: "Reset form")
		url    = the URL where your form is displayed (can use %%current%% for current URL)
		custom = any attributes or custom code for the link element
	
*/
function usp_reset_button_shortcode($args) {
	
	extract(shortcode_atts(array(
		'class'  => '',
		'value'  => __('Reset form', 'usp'),
		'url'    => '#please-check-shortcode',
		'custom' => '',
	), $args));
	
	$protocol = is_ssl() ? 'https://' : 'http://';
	
	$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
	
	$current = isset($_SERVER['REQUEST_URI']) ? $protocol . $host . $_SERVER['REQUEST_URI'] : '';
	
	$url = preg_replace('/%%current%%/', $current, $url);
	
	$url = remove_query_arg(array('usp_reset_form', 'post_id', 'success', 'usp-error'), $url);
	
	$href = get_option('permalink_structure') ? $url .'?usp_reset_form=true"' : $url .'&usp_reset_form=true';
	
	$class = empty($class) ? '' : ' class="'. esc_attr($class) .'"';
	
	$output = '<p'. $class .'><a href="'. esc_url($href) .'"'. $custom .'>'. esc_html($value) .'</a></p>';
	
	return $output;
	
}
add_shortcode('usp-reset-button', 'usp_reset_button_shortcode');



/*
	Displays a list of all user submitted posts
	Bonus: includes any posts submitted by the Pro version of USP :)
	Shortcode: 
		[usp_display_posts userid="current"]          : displays all submitted posts by current logged-in user
		[usp_display_posts userid="1"]                : displays all submitted posts by registered user with ID = 1
		[usp_display_posts userid="Pat Smith"]        : displays all submitted posts by author name "Pat Smith"
		[usp_display_posts userid="all"]              : displays all submitted posts by all users/authors
		[usp_display_posts userid="all" numposts="5"] : limit to 5 posts
		
	Note that the Pro version of USP provides many more options for the display-posts shortcode:
		
		https://plugin-planet.com/usp-pro-display-list-submitted-posts/
	
*/
function usp_display_posts($attr, $content = null) {
	
	global $post;
	
	extract(shortcode_atts(array(
		
		'userid'   => 'all',
		'numposts' => -1
		
	), $attr));
	
	if (ctype_digit($userid)) {
		
		$args = array(
			'author'         => $userid,
			'posts_per_page' => $numposts,
			'meta_key'       => 'is_submission',
			'meta_value'     => '1'
		);
		
	} elseif ($userid === 'all') {
		
		$args = array(
			'posts_per_page' => $numposts,
			'meta_key'       => 'is_submission',
			'meta_value'     => '1'
		);
		
	} elseif ($userid === 'current') {
		
		$args = array(
			'author'         => get_current_user_id(),
			'posts_per_page' => $numposts,
			'meta_key'       => 'is_submission',
			'meta_value'     => '1'
		);
		
	} else {
		
		$args = array(
			'posts_per_page' => $numposts,
			
			'meta_query' => array(
				
				'relation' => 'AND',
				
				array(
					'key'    => 'is_submission',
					'value'  => '1'
				),
				array(
					'key'    => 'user_submit_name',
					'value'  => $userid
				)
			)
		);
		
	}
	
	$submitted_posts = get_posts($args);
	
	$display_posts = '  <table cellpadding="0" cellspacing="0" border="0" style="margin-left:3%;">
      <thead >
        <tr style="background-color:#1E73BE; color:white;">
          <th>Titre</th>
          <th>Auteur</th>
		  <th>Date</th>
          <th>Categorie</th>
		  <th>Tags</th>
          <th>comments</th>
          <th>Statut</th>
		  <th>Edition</th>
        </tr>
      </thead> ';
	
	foreach ($submitted_posts as $post) {
		$posttags=get_the_tags();
		setup_postdata($post);
		 foreach((get_the_category()) as $cat) { 
$cato=$cat->cat_name; 
			 
};
		$content='[user-submitted-posts]';
		 wp_update_post($content);
		if ($posttags) {
			foreach($posttags as $tag) {
			  $tage=$tag->name . ' '; 
			}}; 
		$id=get_the_ID();
		$display_posts .= '<tbody>
		<tr> <input type=hidden id="post" value=<?php echo $id; ?>
		<input type=hidden id="postdeux" value=<?php echo $submitted_posts; ?>
		<td>'.get_the_title() .' </td>
		<td>'.get_the_author().'</td>
		<td> '.get_the_date(). '</td>
		<td> '.  $cato.'</td>
		<td>'.$tage.'</td>
		<td> '. get_comments_number(). ' </td>
		<td>'. get_post_status().'</td>
		<td>
		<a href="#" >modifier</a> 
		</td>
		</tr> 
		</br>'; 
		  $my_post = [
      'ID'           => $id,
      'post_content' => $content,
    ];

    // Update the post into the database
    wp_update_post( $my_post );

		
		
		
	}
	
	$display_posts .= '</tbody></table>';
	
	wp_reset_postdata();
	
	return $display_posts;
	
}
add_shortcode('usp_display_posts', 'usp_display_posts');



/* 
	Shortcode: [usp_gallery]
	Displays a gallery of submitted images for the current post
	Syntax: [usp_gallery size="" before="" after="" number="" id=""]
	Notes: 
		Use curly brackets to output angle brackets
		Use single quotes in before/after attributes
		See usp_get_images() for inline notes and more infos
*/
if (!function_exists('usp_gallery')) :

function usp_gallery($attr, $content = null) {
	
	extract(shortcode_atts(array(
		
		'size'   => 'thumbnail',
		'before' => '{a href="%%url%%"}{img src="',
		'after'  => '" /}{/a}',
		'number' => false,
		'id'     => false,
		
	), $attr));
	
	$images = usp_get_images($size, $before, $after, $number, $id);
	
	$gallery = '';
	
	foreach ($images as $image) $gallery .= $image;
	
	$gallery = $gallery ? '<div class="usp-image-gallery">'. $gallery .'</div>' : '';
	
	return $gallery;
	
}
add_shortcode('usp_gallery', 'usp_gallery');

endif;