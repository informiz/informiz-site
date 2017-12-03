<?php

include_once get_stylesheet_directory() . '/informiz_object_init.php';

function iz_getVersion($fullpath) {
	return filemtime($fullpath);
}

function iz_init_user( $user_id ) {
	update_user_meta($user_id, 'reputation', '0.5');
	update_user_meta($user_id, 'scholar_verified', 'false');
}
add_action( 'user_register', 'iz_init_user', 10, 1 );

function iz_tipso_scripts() {
	wp_register_script('dotimeout', get_stylesheet_directory_uri(). '/inc/tools/doTimeout.js', array('jquery'),'1.0', true);
	wp_enqueue_script('dotimeout');
	wp_register_script('tipso', get_stylesheet_directory_uri() . '/inc/tools/tipso.js', array('jquery', 'dotimeout'),'1.0.4', true);
	wp_enqueue_script('tipso');

	wp_register_style('tipso-style', get_stylesheet_directory_uri() . '/inc/tools/tipso.css');
	wp_enqueue_style('tipso-style');
}
add_action( 'wp_enqueue_scripts', 'iz_tipso_scripts' );


function iz_scripts() {
	wp_register_script('iz-rating', get_stylesheet_directory_uri() . '/inc/common/rating.js', array('jquery', 'tipso'),'0.1.0', true);
	wp_register_script('iz-profile', get_stylesheet_directory_uri() . '/inc/user/profile.js', array('jquery', 'tipso', 'iz-rating'),'0.1.0', true);
	wp_register_script('iz-informi', get_stylesheet_directory_uri() . '/inc/informi/informi.js', array('jquery', 'tipso', 'iz-rating'),'0.1.0', true);
	wp_register_script('bpopup', get_stylesheet_directory_uri() . '/inc/tools/bpopup.js', array('jquery'),'0.11.0.min', true);

	wp_register_style('iz-rating-style', get_stylesheet_directory_uri() . '/inc/common/rating.css');
	wp_enqueue_style('iz-rating-style');

	wp_register_style('iz-informi-style', get_stylesheet_directory_uri() . '/inc/informi/informi.css');
	wp_enqueue_style('iz-informi-style');

	wp_register_style('iz-common', get_stylesheet_directory_uri() . '/inc/common/iz_common.css');
	wp_enqueue_style('iz-common');

	wp_register_style('iz_submit_style', get_stylesheet_directory_uri(). '/inc/informi/submit.css');
	wp_enqueue_style('iz_submit_style');

}
add_action( 'wp_enqueue_scripts', 'iz_scripts' );

function iz_submit_scripts() {
	$jspath = get_stylesheet_directory_uri() . '/inc/informi/submit.js';
	wp_register_script('iz_submit', $jspath, array('jquery'), (string)iz_getVersion(dirname(__FILE__), '/inc/informi/submit.js'), true);
	wp_enqueue_script('iz_submit');
}

add_filter('query_vars', 'iz_add_qvar' );

function iz_add_qvar( $qvars ) {
	$qvars[] = 'informiId';
	return $qvars;
}

include_once get_stylesheet_directory() . '/inc/user/profile.php';

include_once get_stylesheet_directory() . '/inc/informi/informi.php';

add_action( '__after_content' ,'iz_informi_fields' );
 
function iz_informi_fields() {
	global $post;
	if ( !isset($post) || !is_singular() )
		return;

	/*if ('post' == $post -> post_type) {
		iz_add_rating_widget();
	}
	else*/ if ('informi' == $post -> post_type) {
		iz_add_informi_section();
	}
	else if ('submit-informi' == $post -> post_name) {
		iz_submit_scripts();
		require_once get_stylesheet_directory() . '/inc/informi/create-informi.php';		
	}
	else if ('edit-informi' == $post -> post_name) {
		iz_submit_scripts();
		require_once get_stylesheet_directory() . '/inc/informi/update-informi.php';		
	}
	else if ('delete-informi' == $post -> post_name) {
		iz_submit_scripts();
		require_once get_stylesheet_directory() . '/inc/informi/delete-informi.php';		
	}	
}



function submit_informi_redirect() {
	$loggedin = is_user_logged_in(); // TODO: check why i can still view page when logged out! um plugin bug??
	if( ( is_page( 'submit-informi' ) || is_page( 'edit-informi' )  || is_page( 'delete-informi' ) ) && ( ! $loggedin ) )
	{
		wp_redirect( home_url( '/log-in/' ) );
		exit();
	}
}
add_action( 'template_redirect', 'submit_informi_redirect' );

function iz_add_json_endpoint() {
	add_rewrite_endpoint( 'json', EP_ROOT );
}
add_action( 'init', 'iz_add_json_endpoint' );

function iz_json_template_redirect() {
	global $wp_query;
	
	if ( ! isset( $wp_query->query_vars['json'] ) )
		return;

	$id = get_query_var( 'json' );
	if( $id ) {
		$post = get_post( $id );
		if ( $post && 'informi' == $post -> post_type) {
			$mtype = get_post_meta( $post->ID, 'iz_media_type', TRUE );
			$msrc = get_post_meta( $post->ID, 'iz_media_src', TRUE );
			if ( $mtype && $msrc ) { 
				$resp = array('iz_type' => $mtype, 'iz_src' => $msrc);
			} else {
				//TODO this should not happen, log
				$resp = array('iz_error' => 'Failed to retrieve informi data');
			}
		} else {
			$resp = array('iz_error' => 'Json format only available for informiz');
		}
	} else {
		$resp = array('iz_error' => 'Informi id is required');
	}
	
	if(isset($_GET['callback'])){
		echo $_GET['callback'] . '(' . json_encode( $resp ) . ')';
	}
	else {
		echo json_encode( $resp );
	}
	exit;

}
add_action( 'template_redirect', 'iz_json_template_redirect' );

?>