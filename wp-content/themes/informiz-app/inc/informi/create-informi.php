<?php

$userId = get_current_user_id();
$nonceKey = 'informiz_create_' . $userId;

// ----------------------------------- form rendering callbacks -----------------------------------
function iz_getFormAction() {
	return the_permalink();
}

function iz_getTitle() {
	return $_POST['iz_title'];
}

function iz_getDescription() {
	return $_POST['iz_desc'];
}

function iz_selectedMedia() {
	return $_POST['iz_media'];
}

function iz_selectedLanguage() {
	return $_POST['iz_lang'];
}

function iz_selectedCategories() {
	return $_POST['iz_cats'];
}

function iz_getTags() {
	return $_POST['iz_tags'];
}

function iz_getSource($media_type) {
	return $_POST[$media_type];
}

// --------------------------------- end form rendering callbacks ---------------------------------

function iz_renderSuccess($id) { 
	$name = 'user';
	$current_user = wp_get_current_user();
	if ( $current_user instanceof WP_User ) {  // TODO: log if no current user!!
		$name = $current_user->user_login;
	} ?>
	<div class="iz_info">
	<h3>Thanks, <?php echo esc_html( $name ); ?>!</h3>
	<p>Your informi was successfully submitted and is available <a href="<?php echo esc_url( get_permalink($id) ); ?>">here</a>.</p>
	</div> <?php  
}

function iz_renderError($errMsg) { ?>
	<p class="iz_error"><?php echo esc_html( $errMsg ) ?></p> <?php
}

require_once get_stylesheet_directory() . '/inc/informi/informi-editor.php';

if ( ! $userId ) { // TODO this shouldn't happen, log
	iz_renderError( 'You must be logged in to create an informi' );
} else {
	if( iz_formSubmitted() ) {
		$id = iz_submitInformi( $nonceKey );
		if ( $id ) {
			iz_renderSuccess($id);
		} else {
			iz_renderError('There was a problem submitting your informi, please see details below.');
		}
	}
	iz_renderForm($nonceKey);
}

?>