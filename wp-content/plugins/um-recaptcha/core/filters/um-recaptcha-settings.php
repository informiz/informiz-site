<?php

	/***
	***	@extend settings
	***/
	add_filter("redux/options/um_options/sections", 'um_recaptcha_config', 20 );
	function um_recaptcha_config($sections){
		
		$sections[] = array(

			'subsection' => true,
			'title'      => __( 'Google reCAPTCHA','um-recaptcha'),
			'fields'     => array(

				array(
						'id'       		=> 'g_recaptcha_status',
						'type'     		=> 'switch',
						'title'   		=> __( 'Enable Google reCAPTCHA','um-recaptcha' ),
						'default' 		=> 1,
						'desc' 	   		=> __('Turn on or off your Google reCAPTCHA on your site registration and login forms by default.','um-recaptcha'),
				),
		
				array(
						'id'       		=> 'g_recaptcha_sitekey',
						'type'     		=> 'text',
						'title'   		=> __( 'Site Key','um-recaptcha' ),
						'desc' 	   		=> __('You can register your site and generate a site key via <a href="https://www.google.com/recaptcha/">Google reCAPTCHA</a>','um-recaptcha'),
				),
				
				array(
						'id'       		=> 'g_recaptcha_secretkey',
						'type'     		=> 'text',
						'title'   		=> __( 'Secret Key','um-recaptcha' ),
						'desc' 	   		=> __('Keep this a secret. You can get your secret key via <a href="https://www.google.com/recaptcha/">Google reCAPTCHA</a>','um-recaptcha'),
				),
		
			)

		);
		
		return $sections;
		
	}