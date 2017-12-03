jQuery(document).ready(function () {
	jQuery('#iz_media').change(function () {
		media = jQuery(this).val();
		jQuery('.iz_mediaOption').each(function () {
		name = jQuery(this).attr('name');
			if (name == media) {
				jQuery(this).show();
			}
			else {
				jQuery(this).hide();
			}	
		});
	});
	jQuery('input[name="infgrphcs"]').click(function () {
		opt = jQuery(this).val();
		jQuery('.iz_infgrphcsOpt').each(function () {
		id = jQuery(this).attr('id');
			if (id == opt) {
				jQuery(this).show();
			}
			else {
				jQuery(this).hide();
			}	
		});
	});
	if (! jQuery('#iz_lang').val()) {
		jQuery('#iz_lang').val('en');
	}
	if (jQuery('#iz_media').val()) {
		jQuery('#iz_media').trigger('change');
	} else {
		jQuery('#iz_media').val('prezi').trigger('change');
	}
	
	jQuery('.iz_helpIcon').each(function () {
		jQuery(this).tipso({
		background: '#555555',
		color: '#fff'
		});	
	});
	// TODO this is a bit ugly. better way to attach tooltip?
	jQuery('.iz_mediaHelp').tipso({
		width:250,
		content: jQuery('#iz-media_tip').prop('innerHTML'), 
		background: '#555555',
		color: '#fff'
	});
});