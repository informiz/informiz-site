<?php

	/***
	***	@custom error
	***/
	add_filter('um_custom_error_message_handler', 'um_recaptcha_custom_error', 10, 2 );
	function um_recaptcha_custom_error( $msg, $err_t ) {
		if ( $err_t == 'recaptcha' )
			$msg = __('Please confirm you are not a robot','um-recaptcha');
		return $msg;
	}