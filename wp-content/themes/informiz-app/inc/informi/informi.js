jQuery(document).ready(function () {
	styleRatingConfidence('#rating', jQuery('#rating').attr( 'data-rating-conf' ));
	styleRatingConfidence('#approval', jQuery('#approval').attr( 'data-rating-conf' ));

	attachTooltip('#rating', createConfLegend(jQuery('#rating').attr('title')));
	attachTooltip('#approval', createConfLegend(jQuery('#approval').attr('title')));

	attachTooltip('#iz-like', jQuery('#iz-like').attr('title'), 'auto');
	attachTooltip('#iz-dislike', jQuery('#iz-dislike').attr('title'), 'auto');

	attachTooltip('#iz-approve', jQuery('#iz-like').attr('title'), 'auto');
	attachTooltip('#iz-reject', jQuery('#iz-dislike').attr('title'), 'auto');
	
	var del = jQuery('#iz-delete');
	if ( del.length ) {
		del.on('click', function(e) {
			e.preventDefault();
	
			// Triggering bPopup when click event is fired
			var bPopup = jQuery('.iz_popupContainer').bPopup();
			jQuery('#iz_cancel').on('click', function(e) {
				e.preventDefault();
				bPopup.close();
			});
			jQuery('#iz_delete').on('click', function(e) {
				e.preventDefault();
				bPopup.close();
				window.location.replace(jQuery(this).attr("title"));
			});
		});
	}
});
		

function attachTooltip(selector, the_content, the_width) {
	if (the_width === undefined) {
		  the_width = 250;
	}
	jQuery(selector).tipso({
		content: the_content, 
		width:the_width,
		background: '#555555',
		color: '#FFFFFF',
		delay: 500,
		toggleAnimation : true
	});
}