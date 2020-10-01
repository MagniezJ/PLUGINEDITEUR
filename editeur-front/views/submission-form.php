<?php // User Submitted Posts - Submission Form

if (!defined('ABSPATH')) die();

if (isset($usp_options['logged_in_users']) && $usp_options['logged_in_users'] && !is_user_logged_in()) : 
	
	echo usp_login_required_message();
	
else : 
	
	extract(usp_get_form_vars());
	
?>

<!-- User Submitted Posts @ https://m0n.co/usp -->

<div id="user-submitted-posts">
	<?php if ($usp_options['usp_form_content'] !== '') echo $usp_options['usp_form_content']; ?>

	<form id="usp_form" method="post" enctype="multipart/form-data" action="">
		<div id="usp-error-message" class="usp-callout-failure usp-hidden">
		<!-- changement du message d erreur -->	
		<?php esc_html_e('Entrer les champs requis: Titre, Contenu, Nom, Image mise en avant', 'usp'); ?></div>
		<?php echo usp_error_message(); ?>
		<?php
		if (isset($_GET['success']) && $_GET['success'] == '1') :
			echo '<div id="usp-success-message">'. $usp_options['success-message'] .'</div>';
		else :
		
		if (($usp_options['usp_name'] == 'show' || $usp_options['usp_name'] == 'optn') && ($usp_display_name)) { ?>

		<fieldset class="usp-name" style="width:30%; margin-left:10%; margin-top:2%; margin-bottom:0%">
			<label for="user-submitted-name" style="display:none;"><?php esc_html_e('Your Name', 'usp'); ?></label>
			<input id="user-submitted-name" name="user-submitted-name" type="text" value=""
				style="    width: 20%;
    position: absolute;
    top: 4%;
    left: 60%;"
				placeholder="<?php esc_attr_e('Your Name', 'usp'); ?>"
				<?php if (usp_check_required('usp_name')) echo $usp_required; ?> class="usp-input">
		</fieldset>
		<?php } if (($usp_options['usp_email'] == 'show' || $usp_options['usp_email'] == 'optn') && ($usp_display_email)) { ?>

		<fieldset class="usp-email">
			<label for="user-submitted-email"><?php esc_html_e('Your Email', 'usp'); ?></label>
			<input id="user-submitted-email" name="user-submitted-email" type="email" data-parsley-type="email" value=""
				placeholder="<?php esc_attr_e('Your Email', 'usp'); ?>"
				<?php if (usp_check_required('usp_email')) echo $usp_required; ?> class="usp-input">
		</fieldset>
		<?php } if (($usp_options['usp_url'] == 'show' || $usp_options['usp_url'] == 'optn') && ($usp_display_url)) { ?>

		<fieldset class="usp-url">
			<label for="user-submitted-url"><?php esc_html_e('Your URL', 'usp'); ?></label>
			<input id="user-submitted-url" name="user-submitted-url" type="url" data-parsley-type="url" value=""
				placeholder="<?php esc_attr_e('Your URL', 'usp'); ?>"
				<?php if (usp_check_required('usp_url')) echo $usp_required; ?> class="usp-input">
		</fieldset>
		<?php } if ($usp_options['usp_title'] == 'show' || $usp_options['usp_title'] == 'optn') { ?>

		<fieldset class="usp-title" style="margin-left:10%; width: 50%">
			<label for="user-submitted-title"
				style="font-size:36px; margin-top: 2%; margin-bottom:5%; margin-left:10%;"><?php esc_html_e('Ajouter un article', 'usp'); ?></label>
			<input id="user-submitted-title" name="user-submitted-title" type="text" value=""
				style="width: 60%;
    margin-left: 19%;" placeholder="<?php esc_attr_e('Post Title', 'usp'); ?>"
				<?php if (usp_check_required('usp_title')) echo $usp_required; ?> class="usp-input">
		</fieldset>
		<?php } if ($usp_options['usp_tags'] == 'show' || $usp_options['usp_tags'] == 'optn') { ?>

		<?php } if ($usp_options['custom_field'] == 'show' || $usp_options['custom_field'] == 'optn') { ?>

		<fieldset class="usp-custom">
			<label for="user-submitted-custom"><?php echo esc_html($usp_custom_label); ?></label>
			<input id="user-submitted-custom" name="<?php echo esc_attr($usp_custom_name); ?>" type="text" value=""
				placeholder="<?php echo esc_attr($usp_custom_label); ?>"
				<?php if (usp_check_required('custom_field')) echo $usp_required; ?> class="usp-input">
		</fieldset>
		<?php } if ($usp_options['usp_captcha'] == 'show') { ?>

		<fieldset class="usp-captcha">
			<label for="user-submitted-captcha"><?php echo $usp_options['usp_question']; ?></label>
			<input id="user-submitted-captcha" name="user-submitted-captcha" type="text" value=""
				placeholder="<?php esc_attr_e('Antispam Question', 'usp'); ?>" <?php echo $usp_required; ?>
				class="usp-input<?php echo $usp_captcha; ?>" data-parsley-excluded="true">
		</fieldset>
		<?php } if (($usp_options['usp_category'] == 'show' || $usp_options['usp_category'] == 'optn') && ($usp_options['usp_use_cat'] == false)) { ?>


		<?php } if ($usp_options['usp_content'] == 'show' || $usp_options['usp_content'] == 'optn') { ?>

		<fieldset class="usp-content" style="width:40%;margin-top:5%; margin-left:15%">
			<?php } if ($usp_options['usp_content'] == 'show' || $usp_options['usp_content'] == 'optn') { ?>

			<fieldset class="usp-content">
				<?php if ($usp_options['usp_richtext_editor'] == true) { ?>

				<div class="usp_text-editor">
					<?php $usp_rte_settings = array(
				    'wpautop'          => true,  // enable rich text editor
				    'media_buttons'    => true,  // enable add media button
				    'textarea_name'    => 'user-submitted-content', // name
				    'textarea_rows'    => '15',  // number of textarea rows
				    'tabindex'         => '',    // tabindex
				    'editor_css'       => '',    // extra CSS
				    'editor_class'     => 'usp-rich-textarea', // class
				    'teeny'            => false, // output minimal editor config
				    'dfw'              => false, // replace fullscreen with DFW
				    'tinymce'          => true,  // enable TinyMCE
				    'quicktags'        => true,  // enable quicktags
				    'drag_drop_upload' => true,  // enable drag-drop
				);
				$usp_rte_settings = apply_filters('usp_editor_settings', $usp_rte_settings);
				$usp_editor_content = apply_filters('usp_editor_content', '');
				wp_editor($usp_editor_content, 'uspcontent', $usp_rte_settings); ?>

				</div>
				<?php } else { ?>

				<textarea style="height:20%; margin-top:8%; width:50%" id="user-submitted-content"
					name="user-submitted-content" rows="20" placeholder="<?php esc_attr_e('Post Content', 'usp'); ?>"
					<?php if (usp_check_required('usp_content')) echo $usp_required; ?> class="usp-textarea"></textarea>
				<?php } ?>


				<fieldset class="usp-category" style="font-size: 20px; position:absolute; left:60%; top:10%">
					<label for="user-submitted-category" style="margin-bottom: 2%">
						<?php esc_html_e('Catégorie', 'usp'); ?></label>
					<ul id=" user-submitted-category[]" style="display:block;list-style-type: none; " <?php if (usp_check_required('usp_category')) 
			echo $usp_required; echo $multiple_cats . $category_class; ?>>
						<?php echo usp_get_cat_options(); ?>
					</ul>
				</fieldset>

				<?php if ($usp_existing_tags) { ?>

				<fieldset class="usp-tags" >
					<label style=" position: absolute;
    top: 57%;
    left: 60%;"> Etiquettes</label>
					<ul id="user-submitted-tags" name="user-submitted-tags[]"
						<?php if (usp_check_required('usp_tags')) echo $usp_required; ?>
						 class="usp-select usp-multiple"
						multiple="multiple" style="    position: absolute;
    top: 62%;
    left: 60%;
    width: 30%; list-style-type: none;
}"
						>
						
						<?php echo usp_get_tag_options(); ?>
					</ul>
				</fieldset>
				<?php } else { ?>

					<fieldset class="usp-tags" >
						<label style=" position: absolute;
    top: 57%;
    left: 60%;"> Etiquettes</label>
					<ul id="user-submitted-tags" name="user-submitted-tags[]"
						<?php if (usp_check_required('usp_tags')) echo $usp_required; ?>
						 class="usp-select usp-multiple"
						multiple="multiple" 
						style="    position: absolute;
    top: 61%;
    left: 60%;
    width: 30%; list-style-type: none;
}"
						>
						
						<?php echo usp_get_tag_options(); ?>
					</ul>
				<?php } ?>
			</fieldset>
			<?php } if ($usp_recaptcha_public && $usp_recaptcha_private && $usp_recaptcha_display == 'show' && $usp_recaptcha_version == 2) { ?>

			<fieldset class="usp-recaptcha">
				<label for="g-recaptcha"><?php esc_html_e('Verification', 'usp'); ?></label>
				<div id="g-recaptcha" class="g-recaptcha" data-sitekey="<?php echo esc_attr($usp_data_sitekey); ?>">
				</div>
			</fieldset>
			<?php }  ?>

			<fieldset class="usp-images">
				<div class="form-group">
					<label for="title">Image mise en avant</label>
					<input type="file" 
					class="form-control" id="thumbnail" 
					name="thumbnail" accept="image/png, image/jpeg, image/jpg, image/svg">
					<?php if (usp_check_required('usp_images')) echo $usp_required; ?>
					<div class="image-preview" id="imagePreview" 
					style="width: 300px; min-height:100px;border: 2px solid white;
					 margin-top: 15px; display:flex; align-items:center; 
					 justify-content:center; color:grey;">
						<img src="" alt="Image Previex"  
						class="image-preview__image" style="display:none; 
						width: 100%;">
						<span id="text" class="image-preview__default-text">Image Preview</span>
					</div>
				</div>

			</fieldset>


			<fieldset id="usp_verify" style="display:none;">
				<label
					for="user-submitted-verify"><?php esc_html_e('Human verification: leave this field empty.', 'usp'); ?></label>
				<input id="user-submitted-verify" name="user-submitted-verify" type="text" value=""
					data-parsley-excluded="true">
			</fieldset>

			<?php echo usp_display_custom_checkbox(); ?>

			<div id="usp-submit" style="position: absolute;
    top: 93%;
    left: 15%;">
				<?php if (isset($usp_options['redirect-url']) && !empty($usp_options['redirect-url'])) { ?>

				<input type="hidden" class="usp-hidden" name="redirect-override"
					value="<?php echo esc_url($usp_options['redirect-url']); ?>">
				<?php } ?>
				<?php if (!$usp_display_name) { ?>

				<input type="hidden" class="usp-hidden" name="user-submitted-name"
					value="<?php echo esc_attr($usp_user_name); ?>">
				<?php } ?>
				<?php if (!$usp_display_email) { ?>

				<input type="hidden" class="usp-hidden" name="user-submitted-email"
					value="<?php echo sanitize_email($usp_user_email); ?>">
				<?php } ?>
				<?php if (!$usp_display_url) { ?>

				<input type="hidden" class="usp-hidden" name="user-submitted-url"
					value="<?php echo esc_url($usp_user_url); ?>">
				<?php } ?>
				<?php if (isset($usp_options['usp_use_cat']) && 
			$usp_options['usp_use_cat'] == true) { ?>

				<input type="hidden" class="usp-hidden" name="user-submitted-category"
					value="<?php echo esc_attr($usp_options['usp_use_cat_id']); ?>">
				<?php } ?>

				<input type="submit" 
				class="usp-submit" id="user-submitted-post" name="user-submitted-post"
					value="<?php esc_attr_e('Soumettre l\'article', 'usp'); ?>">
				<?php wp_nonce_field('usp-nonce', 'usp-nonce', false); ?>

			</div>

			<?php endif; ?>

	</form>
</div>
<script>
	(function () {
		var e = document.getElementById('usp_verify');
		if (e) e.parentNode.removeChild(e);
	})();
</script>

<?php endif; ?>