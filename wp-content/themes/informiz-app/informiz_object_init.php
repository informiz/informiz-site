<?php

/***
* Create language taxonomy
****/
function create_informi_lang_taxonomy( $slug ) {
	$labels = array(
		'name'                       => 'Language',
		'singular_name'              => 'Language',
		'search_items'               => 'Search Languages',
		'popular_items'              => 'Popular Languages',
		'all_items'                  => 'All Languages',
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'choose_from_most_used'      => 'Choose from the most used languages',
		'not_found'                  => 'No languages found.',
		'menu_name'                  => 'Languages',
	);

	$args = array(
		'hierarchical'          => false,
		'labels'                => $labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'language' ),
	);

	register_taxonomy( $slug, null, $args );
}


/***
* Create Informi custom post type
****/
function create_informi_post_type ( $slug, $taxonomies ) {
	$labels = array(
		'name'               => 'Informiz',
		'singular_name'      => 'Informi',
		'menu_name'          => 'Informiz',
		'name_admin_bar'     => 'Informi',
		'add_new'            => 'Add new',
		'add_new_item'       => 'Add new informi',
		'new_item'           => 'New informi',
		'edit_item'          => 'Edit informi',
		'view_item'          => 'View informi',
		'all_items'          => 'All Informiz',
		'search_items'       => 'Search Informiz',
		'parent_item_colon'  => 'Parent Informiz:',
		'not_found'          => 'No informiz found.',
		'not_found_in_trash' => 'No informiz found in Trash.'
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'has_archive'        => true,
		'hierarchical'       => false,
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'revisions'),
		'taxonomies'         => $taxonomies
	);

	register_post_type( $slug, $args );
}


function iz_create_post_type() {
	$lang_taxonomy_slug = 'iz_lang';
	$informi_slug = 'informi';
	
	create_informi_lang_taxonomy( $lang_taxonomy_slug );
	create_informi_post_type( $informi_slug, array( 'category', 'post_tag', $lang_taxonomy_slug) );
}

function iz_rewrite_flush() {
    flush_rewrite_rules();
}

add_action( 'init', 'iz_create_post_type' );
add_action( 'after_switch_theme', 'iz_rewrite_flush' );

/***
* Add informiz custom post type to search results
****/
function add_informi_type_to_tax( $query ) {
	if( is_category() || is_tag() && empty( $query->query_vars['suppress_filters'] ) ) {
		$post_types = get_post_types();
		$query->set( 'post_type', $post_types );
		return $query;
	}
}

add_filter( 'pre_get_posts', 'add_informi_type_to_tax' );

/***
* Add informiz custom post type to recent posts widget
****/
function add_informi_type_to_recent_posts($args) {
    $args['post_type'] = array('post', 'informi');
    return $args;
}

add_filter( 'widget_posts_args', 'add_informi_type_to_recent_posts');

?>