jQuery(document).ready(function () {
	styleRatingConfidence('.circle-text', jQuery('#reputation').attr( 'data-rating-conf' ));

	jQuery('#reputation').tipso({
		content: createConfLegend(jQuery('#reputation').attr('title')), 
		width:250,
		background: '#555555',
		color: '#FFFFFF',
		delay: 500,
		toggleAnimation : true
	});
});