<?php // User Submitted Posts - Shortcodes misc.
 if(!isset($_SESSION)){
    session_start();
}
function __set( $name , $value )
    {
        $_SESSION[$name] = $value;
    }
    
 //On définit des variables de session
 

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
	$submitted_posts = get_posts();
	
	$display_posts = '<div id="fom" style="display:none;">
	'.do_shortcode("[modification]") .'
	</div>
	<table id="tb" cellpadding="0" cellspacing="0" border="0" >
    <thead >
        <tr style="background-color:#1E73BE; color:white;">
            <th>Titre</th>
            <th>Auteur</th>
		    <th>Date</th>
            <th>Categorie</th>
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
		$id="ID".get_the_id();
		if ($posttags) {
			foreach($posttags as $tag) {
			$tage=$tag->name . ' '; 
			}}
		
		$idp = get_the_ID();
		$content_post = get_post($idp);
$content = $content_post->post_content;
$content = apply_filters('the_content', $content);
$content = str_replace(']]>', ']]&gt;', $content);

			$display_posts .= '
			<tbody>
			<tr>
				<input type=hidden id="post" value=<?php  $id; ?>
				<input type=hidden id="postdeux" value=<?php  $submitted_posts; ?> 
				<td><p id="title">'.get_the_title() .'</p> </td>
				<td><p id="aut">'.get_the_author().'</p></td>
				<td> '.get_the_date(). '</td>
				<td><p id="cat"> '. $cato.'</p></td>
				<td> '. get_comments_number(). ' </td>
				<td>'. get_post_status().'</td>
				<td><a href="#" class="mod">modifier</a> </br>
				<a onclick="return confirm(\'Etes vous sur de vouloir supprimer le post : '. get_the_title().'?\')"  href="'.get_delete_post_link(get_the_ID()).'" data-id="'. the_ID() .'?>" data-nonce="'. wp_create_nonce('ajax_delete_post_nonce') .'" class="delete-post"
				style="text-decoration:none; color:red; font-weight:bolder; border: 2px solid red;">
				delete</>
				</td>
				<td style="display:none;"><p id="img" style="display:none;">'.get_the_post_thumbnail().'</p></td>
				<td style="display:none;"><p id="content" style="display:none;">'.$content.'</p></td>
			</tr>
			</br>';
			$idp = get_the_ID();
			__set($id,$idp);
			$catp="author";
			$aut=get_the_author();
			__set($catp,$aut);
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

'size' => 'thumbnail',
'before' => '{a href="%%url%%"}{img src="',
'after' => '" /}{/a}',
'number' => false,
'id' => false,

), $attr));

$images = usp_get_images($size, $before, $after, $number, $id);

$gallery = '';

foreach ($images as $image) $gallery .= $image;

$gallery = $gallery ? '<div class="usp-image-gallery">'. $gallery .'</div>' : '';

return $gallery;

}
add_shortcode('usp_gallery', 'usp_gallery');

endif;


function modification($args) {

	
$show='<div id="user-submitted-posts">

<form id="usp_form" method="post" enctype="multipart/form-data" action="">
	<fieldset class="usp-name" style="width:30%; margin-left:10%; margin-top:2%; margin-bottom:0%">
		
		<input id="user-submitted-name" name="user-submitted-name" type="text" value=""
			style="width:30%; position:absolute; top:20%;left:20%;"
			placeholder="prenom"
			
	</fieldset>
	<fieldset class="usp-title" style="margin-left:10%; width: 100%">
			<label for="user-submitted-title"
				style="font-size:36px; margin-top: 2%; margin-bottom:5%; 
				margin-left:10%;">Ajouter un titre</label>
			<input id="user-submitted-title" name="user-submitted-title" 
			type="text" value="" style="width: 70%; margin-left:15%" placeholder="Ajouter un titre"
	</fieldset>
		<textarea style="height:20%; margin-top:8%;
		width:100%" id="user-submitted-content"
		name="user-submitted-content" rows="20" placeholder="Poster un article">
		</textarea>
		<fieldset class="usp-category" style="font-size: 20px; position:absolute; left:60%; top:10%">
			<label for="user-submitted-category" style="margin-bottom: 2%">
			Catégorie</label>
			<ul id=" user-submitted-category[]" 
			style="display:block;list-style-type: none; " >
			'. usp_get_cat_options() .'
			</ul>
		</fieldset>
		<fieldset class="usp-tags" style="width:18% ; position:absolute; top:70%; left:60%;">
					<ul id="user-submitted-tags" name="user-submitted-tags[]"
						class="usp-select usp-multiple" multiple="multiple"
						style="width:20%; position:absolute; top:50%; left:63%;">'.
						usp_get_tag_options().'
					</ul>
				</fieldset>
				<fieldset class="usp-images">
				<div class="form-group">
					<label for="title">Image mise en avant</label>
					<input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/png, image/jpeg, image/jpg, image/svg">
					<div class="image-preview" id="imagePreview" 
					style="width: 300px; min-height:100px;border: 2px solid white;
					margin-top: 15px; display:flex; align-items:center; 
					justify-content:center; color:grey;">
						<img src="" alt="Image Previex" id="modifimg" 
						class="image-preview__image" style="display:none; 
						width: 100%;">
						<span id="text" class="image-preview__default-text">Image Preview</span>
					</div>
				</div>

			</fieldset>
			<input type="hidden" name="submitted" id="submitted" value="true" />
			<button type="submit" name="modif">Modifier</button>
			</form>
			</div>
					';
					wp_nonce_field( 'post_nonce', 'post_nonce_field' );
					
	return $show;
};
add_shortcode('modification', 'modification');