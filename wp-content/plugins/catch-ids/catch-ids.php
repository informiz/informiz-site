<?php
/*
Plugin Name: Catch IDs
Plugin URI: https://catchthemes.com/wp-plugins/catch-ids/
Description: Catch IDs is a simple and light weight plugin to show the Post ID, Page ID, Media ID, Links ID, Category ID, Tag ID and User ID in the Admin Section Table. This plugin was initially develop to support our themes features slider. Then we thought that this will be helpful to all the WordPress Admin Users. Just activate and catch IDs in your page, post, category, tag and media pages.
Version: 1.4.6
License: GNU General Public License, version 3 (GPLv3)
License URI: http://www.gnu.org/licenses/gpl-3.0.txt
Author: Catch Themes
Author URI: https://catchthemes.com
Text Domain: catch-ids
Tags: catch-ids, simple, admin, wp-admin, show, ids, post, page, category, media, links, tag, user, id, post id, page id, category id
*/

/*
Copyright (C) 2012-2017 Catch Themes, (info@catchthemes.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Make plugin available for translation
 * Translations can be filed in the /languages/ directory
 */
function catchids_load_textdomain() {
	load_plugin_textdomain( 'catch-ids', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'catchids_load_textdomain' );


/**
 * @package Catch Themes
 * @subpackage Catch IDs
 * @since Catch IDs 1.0
 */

if ( ! function_exists( 'catchids_column' ) ):
/**
 * Prepend the new column to the columns array
 */
function catchids_column($cols) {
	$column_id = array( 'catchids' => __( 'ID', 'catch-ids' ) );
	$cols = array_slice( $cols, 0, 1, true ) + $column_id + array_slice( $cols, 1, NULL, true );
	return $cols;
}

endif; // catchids_column

if ( ! function_exists( 'catchids_value' ) ) :
/**
 * Echo the ID for the new column
 */
function catchids_value( $column_name, $id ) {
	if ( 'catchids' == $column_name ) {
		echo $id;
	}
}
endif; // catchids_value


if ( ! function_exists( 'catchids_return_value' ) ) :
function catchids_return_value( $value, $column_name, $id ) {
	if ( 'catchids' == $column_name ) {
		$value .= $id;
	}
	return $value;
}
endif; // catchids_return_value


if ( ! function_exists( 'catchids_css' ) ) :
/**
 * Output CSS for width of new column
 */
function catchids_css() {
?>
<style type="text/css">
    #catchids { width: 80px; }
    @media screen and (max-width: 782px) {
        .wp-list-table #catchids, .wp-list-table #the-list .catchids { display: none; }
        .wp-list-table #the-list .is-expanded .catchids {
            padding-left: 30px;
        }
    }
</style>
<?php
}
endif; // catchids_css
add_action( 'admin_head', 'catchids_css');


if ( ! function_exists( 'catchids_add' ) ) :
/**
 * Actions/Filters for various tables and the css output
 */
function catchids_add() {
	// For Media Management
	add_action( "manage_media_columns" ,        'catchids_column' );
	add_filter( "manage_media_custom_column" , 'catchids_value' , 10 , 3 );

	// For Link Management
	add_action( 'manage_link_custom_column', 'catchids_value', 10, 2 );
	add_filter( 'manage_link-manager_columns', 'catchids_column' );

	// For Category Management
	add_action( 'manage_edit-link-categories_columns', 'catchids_column' );
	add_filter( 'manage_link_categories_custom_column', 'catchids_return_value', 10, 3 );

	// For Category, Tags and other custom taxonomies Management
	foreach( get_taxonomies() as $taxonomy ) {
		add_action( "manage_edit-${taxonomy}_columns" ,  'catchids_column' );
		add_filter( "manage_${taxonomy}_custom_column" , 'catchids_return_value' , 10 , 3 );
		if( version_compare($GLOBALS['wp_version'], '3.0.999', '>') ) {
			add_filter( "manage_edit-${taxonomy}_sortable_columns" , 'catchids_column' );
		}
	}

	foreach( get_post_types() as $ptype ) {
		add_action( "manage_edit-${ptype}_columns" , 'catchids_column' );
		add_filter( "manage_${ptype}_posts_custom_column" , 'catchids_value' , 10 , 3 );
		if( version_compare($GLOBALS['wp_version'], '3.0.999', '>') ) {
			add_filter( "manage_edit-${ptype}_sortable_columns" , 'catchids_column' );
		}
	}

	// For User Management
	add_action( 'manage_users_columns', 'catchids_column' );
	add_filter( 'manage_users_custom_column', 'catchids_return_value', 10, 3 );
	if( version_compare($GLOBALS['wp_version'], '3.0.999', '>') ) {
		add_filter( "manage_users_sortable_columns" , 'catchids_column' );
	}

	// For Comment Management
	add_action( 'manage_edit-comments_columns', 'catchids_column' );
	add_action( 'manage_comments_custom_column', 'catchids_value', 10, 2 );
	if( version_compare($GLOBALS['wp_version'], '3.0.999', '>') ) {
		add_filter( "manage_edit-comments_sortable_columns" , 'catchids_column' );
	}
}
endif; // catchids_add
add_action( 'admin_init', 'catchids_add' );