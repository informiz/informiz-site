<?php

function iz_profile_fields( $atts, $content = null ) {
	global $ultimatemember;
	$rep = get_the_author_meta( 'reputation', $ultimatemember->user->target_id );
	if (empty($rep) && $rep !== '0') {
		$rep = 0.5;
	}
	$rep = ($rep * 100) . '%';
	
	$the_title = 'This value is based on 0 ratings';
	if ( get_the_author_meta( 'scholar_verified', $ultimatemember->user->target_id ) === 'true') { 
		$the_title .= ', scholar credentials verified'; 
	} 
?>

<div id="rating" class="rank-container">
	<div class="rank-text-container">
		<span class="rank-text">Reputation: </span>
	</div>
	<div class="rank-rating">
		<div id="reputation" class="circle-text" data-rating-conf="0.1"  title="<?php echo esc_attr($the_title); ?>" >
			<div style="font-size:0.8em;"><?php echo esc_html($rep); ?></div>
		</div>
	</div>
</div>	
<?php
	wp_enqueue_script('iz-rating');
	wp_enqueue_script('iz-profile');
}

add_action('um_after_profile_header_name', 'iz_profile_fields');


function iz_render_submit_for_author() {
	if ( um_profile_id() != get_current_user_id() ) return;

	$addInformi = "";
	$submitPage = get_page_by_title( 'Submit Informi' );
	if ( $submitPage ) { ?>
		<div style="padding:4px;">
			<a href="<?php echo esc_url( get_permalink( $submitPage->ID ) ); ?>"> Add Informi &#187; </a>
		</div>
	<?php } // TODO else - log
}


function iz_profile_controllers( $atts, $content = null ) {
	global $ultimatemember;

	if ( $ultimatemember->fields->editing == TRUE ) return;

	iz_render_submit_for_author();
}

add_action('um_profile_before_header', 'iz_profile_controllers');

add_filter('um_profile_tabs', 'iz_informiz_tab', 1000 );
function iz_informiz_tab( $tabs ) {

	$tabs['informiztab'] = array(
		'name' => 'Informiz',
		'icon' => 'um-faicon-search-plus',
	);
		
	return $tabs;
		
}

add_action('um_profile_content_informiztab_default', 'um_profile_content_informiztab_default');

function um_profile_content_informiztab_default( $args ) {
	global $ultimatemember;
	
	iz_render_submit_for_author();

	$ultimatemember->shortcodes->loop = $ultimatemember->query->make('post_type=informi&posts_per_page=10&offset=0&author=' . um_user('ID') );
	if ( $ultimatemember->shortcodes->loop->have_posts()) {
		$ultimatemember->shortcodes->load_template('profile/posts-single'); ?>
		<div class="um-ajax-items"> <?php 
		if ( $ultimatemember->shortcodes->loop->found_posts >= 10 ) { ?>
			<div class="um-load-items">
				<a href="#" class="um-ajax-paginate um-button" data-hook="um_load_posts" data-args="informi,10,10,<?php echo um_user('ID'); ?>"><?php echo'load more informiz'; ?></a>
			</div>
		<?php } ?>
		</div>
	<?php } else { ?>
		<div class="um-profile-note"><span><?php echo 'No informiz to display'; ?></span></div>
	<?php } 
	wp_reset_postdata();
}

?>