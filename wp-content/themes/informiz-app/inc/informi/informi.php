<?php

function iz_register_rating_resources() {
	wp_enqueue_script('iz-rating');
	wp_enqueue_script('iz-informi');
}

function iz_register_author_resources() {
	wp_enqueue_script('bpopup');
}

function iz_add_author_controllers() {
	$userId = (string) get_current_user_id();
	$informi = get_post();
	if ( ! $informi || ( $informi->post_author !== $userId ) ) {  // TODO log if informi is null
		return;
	} 

	$editPage = get_page_by_title( 'Edit Informi' );
	if ( ! $editPage ) {
		return; // TODO log
	}
	$editLink = get_permalink( $editPage->ID )  . '?informiId=' . $informi->ID;
	$deletePage = get_page_by_title( 'Delete Informi' );
	if ( ! $editPage ) {
		return; // TODO log
	}
	$deleteLink = get_permalink( $deletePage->ID )  . '?informiId=' . $informi->ID;
	?>
	<div style="padding:4px;">
		<a id="iz-edit" class="iz_button" href="<?php echo esc_url( $editLink ); ?>"> Edit &#187; </a>
		<a id="iz-delete" class="iz_button" href=""> Delete &#187; </a>
	</div>	
	<div id="iz-confirm-delete" class="iz_popupContainer" style="display:none">
		<div class="iz_dialog">
			<div class="iz_header">
				<img class="iz_logo" src="http://informiz.org/wp-content/uploads/2015/04/informiz.png" alt="logo"/>
			</div>
			<div class="iz_bordered">
				<div class="iz_msg"><p>Are you sure you want to delete this informi?</p></div>
				<div>
					<button id="iz_cancel" name="iz_cancel" type="button" class="iz_button" >Cancel</button>
					<button id="iz_delete" name="iz_delete" type="button" class="iz_button" title="<?php echo esc_url( $deleteLink ); ?>">Proceed</button>
				</div>
			</div>
		</div>
	</div>
<?php
}

function iz_add_rating_widget() {
	iz_register_rating_resources();
	$rating = (0.5 * 100) . '%';
	$rating_title = '&quot;This value is based on 0 ratings&quot;';
	$approval = (0.5 * 100) . '%';
	$approval_title = '&quot;This value is based on 0 evaluations&quot;';

?>
<div class="rank-container">
	<div class="rank-container" style="padding-right:20px;">
		<div class="rank-text-container">
			<span class="rank-text">Rating: </span>
			<span class="action-container">
				<i id="iz-like" class="um-icon-thumbsup iz-icon"  title="Like"></i>		
				<i id="iz-dislike" class="um-icon-thumbsdown iz-icon" title="Dislike"></i>		
			</span>
		</div>
		<div class="rank-rating">
			<div id="rating" class="circle-text" data-rating-conf="0.1"  title=<?php echo htmlspecialchars_decode($rating_title); ?> >
				<div style="font-size:0.8em;"><?php echo "$rating"; ?></div>
			</div>
		</div>
	</div>	
	<div class="rank-container" style="padding-right:20px;">
		<div class="rank-text-container">
			<span class="rank-text">Scholar Approval: </span>
			<span class="action-container">
				<i id="iz-approve" class="um-icon-thumbsup iz-icon" title="Scholar Approve"></i>		
				<i id="iz-reject" class="um-icon-thumbsdown iz-icon" title="Scholar Reject"></i>		
			</span>
		</div>
		<div class="rank-rating">
			<div id="approval" class="circle-text" data-rating-conf="0.1"  title=<?php echo htmlspecialchars_decode($approval_title); ?> >
				<div style="font-size:0.8em;"><?php echo "$approval"; ?></div>
			</div>
		</div>
	</div> 
</div>	
<?php
}

function iz_add_plugin_tip($mtype, $msrc) {
	$wp_code = '&#91;informiz inf_media="' . $mtype . '" inf_source="' . $msrc . '"&#93; YOUR TEXT HERE &#91;/informiz&#93;';
	$example = '[informiz inf_media="' . $mtype . '" inf_source="' . $msrc . '"]Try it![/informiz]';
?>
<div class="iz_section">
	<h3><a href="http://informiz.org/wp-content/uploads/informiz-plugin/infomiz-wp.zip" title="Download an alpha version of the informiz wordpress plugin"><u>WordPress plugin</u></a> shortcode:</h3>
	<span class="iz_info"><?php echo esc_html($wp_code); ?></span> <?php echo do_shortcode( $example ); ?>
</div>	
<?php
	if(function_exists('informiz_add_tooltip')){
	    echo informiz_add_tooltip('');
	} // else TODO log
}


function iz_add_informi_section() {
	global $wpdb;
	$id = get_the_ID();
	$mtype = get_post_meta( $id, 'iz_media_type', TRUE );
	$msrc = get_post_meta( $id, 'iz_media_src', TRUE );
	if ( ! $mtype || ! $msrc ) { ?>
		<div><div class="iz_error"><?php echo esc_html('Informi source not found'); ?></div></div> 
	<?php 
	} else { 
		$media = $wpdb->get_row( $wpdb->prepare("SELECT embed_template FROM informi_media where name = %s", $mtype));
		if (! $media  || ! $media->embed_template) { ?>
			<div><div class="iz_error"><?php echo esc_html('Informi media type not found'); ?></div></div> 
		<?php 
		} else { 
			iz_register_author_resources();
			iz_add_author_controllers();
			$url = sprintf($media->embed_template, $msrc);?>
			<iframe frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen="" width="800" height="480" src="<?php echo esc_attr($url); ?>"></iframe>
			<?php 
			iz_add_rating_widget(); 
			iz_add_plugin_tip('informiz', $id);
		} 
	} 
}

?>