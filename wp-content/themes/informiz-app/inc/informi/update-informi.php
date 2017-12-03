<?php

$userId = (string) get_current_user_id();
$informiId = get_query_var( 'informiId', '0' );
$informi = get_post($informiId);
$nonceKey = 'iz_edit_' . $informiId . '_' . $userId;

// ----------------------------------- form rendering callbacks -----------------------------------
function iz_getFormAction() {
	global $wp;
//	global $informiId;
//	return  add_query_arg( 'informiId', $informiId, home_url( $wp->request ) );
	return  add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
}

function iz_getTitle() {
	if ( $_POST['iz_title'] ) {
		return $_POST['iz_title'];
	}
	global $informiId;
	$informi = get_post($informiId);
	return apply_filters( 'the_title', $informi->post_title);
}

function iz_getDescription() {
	if ( $_POST['iz_desc'] ) {
		return $_POST['iz_desc'];
	}
	global $informiId;
	$informi = get_post($informiId);
	return wp_strip_all_tags( apply_filters( 'the_content', $informi->post_content ) );
}

function iz_selectedMedia() {
	global $informiId;
	if ( $_POST['iz_media'] ) {
		return $_POST['iz_media'];
	}
	return get_post_meta( $informiId, 'iz_media_type', TRUE );
}

function iz_selectedLanguage() {
	global $informiId;
	if ( $_POST['iz_lang'] ) {
		return $_POST['iz_lang'];
	}
	$lang = get_the_terms( $informiId, 'iz_lang' );
	if ($lang) {
		return $lang[0]->slug;
	}
	return '';
}

function iz_selectedCategories() {
	global $informiId;
	if ( $_POST['iz_cats'] ) {
		return $_POST['iz_cats'];
	}
	// TODO check if accessible via $informi->tags_input
	$categories = get_the_category($informiId);
	$cats = array();
	if($categories){
		foreach($categories as $category) {
			$cats[] = $category->term_id;
		}
	}
	return $cats;
}

function iz_getTags() {
	global $informiId;
	if ( $_POST['iz_tags'] ) {
		return $_POST['iz_tags'];
	}
	// TODO check if accessible via $informi->tags_input
	$tags = get_the_terms( $informiId, 'post_tag' );
	if ($tags) {
		foreach ( $tags as $tag ) {
			$tagNames[] = $tag->name;
		}
		return implode( ", ", $tagNames );
	}
	return '';
}

function iz_getSource($mtype) {
	global $informiId;
	global $wpdb;
	if ( $_POST[$mtype] ) {
		return $_POST[$mtype];
	}
	$curType = get_post_meta( $informiId, 'iz_media_type', TRUE );
	if( $mtype !== $curType ) {
		return '';
	}
	$src = get_post_meta( $informiId, 'iz_media_src', TRUE );
	$template = $wpdb->get_col( $wpdb->prepare("SELECT submit_template FROM informi_media where name = %s", $mtype));
	if (! $src  || ! $template) {
		return ''; // TODO log
	}
	return sprintf( $template[0], $src );
}

// --------------------------------- end form rendering callbacks ---------------------------------

function iz_renderSuccess($id) { ?>
	<div class="iz_info">
	<p>Your informi was successfully updated.</p>
	<p><a href="<?php echo esc_url( get_permalink($id) ); ?>"> Go to informi &#187;</a></p>
	</div> <?php  
}

function iz_renderError($errMsg) { ?>
	<p class="iz_error"><?php echo esc_html( $errMsg ) ?></p> <?php
}

if ( ! $informiId || ! $informi ) {
	iz_renderError( 'Informi not found.' );
} else 	if ( $informi->post_author !== $userId ) {
	iz_renderError( 'You are not authorized to edit this informi.' );
} else {
	require_once get_stylesheet_directory() . '/inc/informi/informi-editor.php';

	if( iz_formSubmitted() ) {
		if (iz_submitInformi( $nonceKey, $informiId )) {
			iz_renderSuccess($informiId);
		} else {
			iz_renderError('There was a problem updating your informi, please see details below.');
		}
	}
	iz_renderForm($nonceKey);
}

?>