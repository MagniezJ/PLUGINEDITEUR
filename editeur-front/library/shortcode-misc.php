<?php // User Submitted Posts - Shortcodes misc.
 if(!isset($_SESSION)){
    session_start();
}
function __set( $name , $value )
    {
        $_SESSION[$name] = $value;
    }
    //fonction pour envoyer id de l article a la bdd
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
	//shortcode liste d'artciles
	
	global $post; 
	$submitted_posts = get_posts();
	 //div id=fom -> formulaire modification
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
	$i=0;
	foreach ($submitted_posts as $post) { //boucles pour recuperation des articles
		
		$posttags=get_the_tags(); //recuperations des etiquettes
	    setup_postdata($post);
		
		$id="ID".get_the_ID();
		if ($posttags) {
			foreach($posttags as $tag) {
			$tage=$tag->name ; 
			}}
		foreach((get_the_category()) as $cat) {   //récuperation catégories
			$cato=$cat->cat_name; 
		};
			$idp = get_the_ID(); // recuperation de l'id pour la Bd
			__set($id,$idp);
			 //ligne pour chaque articles
			$display_posts .= '
			<tbody>
			<tr class="article">
				<input type=hidden class="post" id="'.$id.'" value="'.$id.'"> </input>'
				./* récupération de l'id pour l ajax */'
				<td><a href="'.get_post_permalink().'" id="title'.$i.'">'.get_the_title() .'</a> </td>
				'
				./* récupération du titre et lien cliquable vers l article */'
				<td><p id="aut'.$i.'">'.get_the_author().'</p></td>
				'
				./* récupération de l'auteur*/'
				<td> '.get_the_date(). '</td>
				'
				./* récupération de la date */'
				<td><p id="cat'.$i.'"> '. $cato.'</p></td>
				'
				./* récupération de la premiere catégorie  */'
				'
				./* récupération de la premiere etiquette  */'
				<td> '. get_comments_number(). ' </td>
				'
				./* récupération du nombre de commentaire  */'
				<td>'. get_post_status().'</td>
				'
				./* récupération du statut du post  */'
				<td><button  id="'.$i.'" class="mod">modifier</button> 
				'
				./* bouton modifier avec Event js ->modifier.js  */'
				<a onclick="return confirm(\'Etes vous sure de vouloir supprimer le post : '
				. get_the_title().'?\')"  href="'.get_delete_post_link(get_the_ID()).'"
				data-nonce="'. wp_create_nonce('ajax_delete_post_nonce') .'" 
				 class="delete-post"  style="color:red; margin-left: 2% ">
				delete</a>
				</td>
				'
				./* rbouton suppression avec lien vers le lien de suppression
				+fonction sur user-submitted-post.php pour redirection directe vers liste-article  */'
				<td style="display:none;"><p id="img'.$i.'" style="display:none;">'.get_the_post_thumbnail_url().'</p></td>
				'
				./* récupération et non affichage de la photo mise en avant  */'
				<td style="display:none;"><p id="content'.$i.'" style="display:none;">'.get_the_content().'</p></td>
				'
				./* récupération et non affichage du contenu */'
			</tr>
			</br>';
			$i=$i+1;
		}
		$display_posts .= '</tbody></table>'; 

		wp_reset_postdata();

		return $display_posts;
}
add_shortcode('usp_display_posts', 'usp_display_posts');

//shortcode du formulaire de modification
function modification($args) {

	$id=get_the_ID();
$show='<div id="user-submitted-posts">

<form id="usp_form" method="post" enctype="multipart/form-data" action="">
	<fieldset class="usp-name" style="width:30%; margin-left:10%; margin-top:2%; margin-bottom:0%">
	<input type=hidden class="post" id="'.$id.'" value="'.$id.'" name="Idp"> </input>
		<input id="name" name="user-submitted-name" type="text" value=""
			style="  width: 20%;
    position: absolute;
    top: 4%;
    left: 60%;"
			placeholder="prenom"
			
	</fieldset>
	<fieldset class="usp-title" style="margin-left:10%; width: 100%">
			<label for="user-submitted-title"
				style="font-size:36px; margin-top: 2%; margin-bottom:5%; 
				margin-left:10%;">Ajouter un titre</label>
			<input id="titre" name="user-submitted-title" 
			type="text" value="" style="width: 60%;
    margin-left: 19%; font-size:20px; font-weight: normal;" placeholder="Ajouter un titre">
	</fieldset>
		<textarea style="height:20%; margin-top:8%;
		width:100%" id="hello"
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
		<fieldset class="usp-tags" >
					<label style=" position: absolute;
    top: 57%;
    left: 60%;"> Etiquettes</label>
					<ul id="user-submitted-tags" name="user-submitted-tags[]"
						class="usp-select usp-multiple"
						multiple="multiple" style="    position: absolute;
    top: 62%;
    left: 60%;
    width: 30%; list-style-type: none;
}"
						>'. usp_get_tag_options().' 
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
			
			<input type="button" 
				class="usp-submit" id="update-post" name="modif"
					value="modifier">
					'.wp_nonce_field( 'post_nonce', 'post_nonce_field' ).'
			</form>
			</div>
					';
	return $show;
};
add_shortcode('modification', 'modification');