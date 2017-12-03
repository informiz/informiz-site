<?php

	/***
	***	@add recaptcha
	***/
	add_action('um_after_register_fields', 'um_recaptcha_add_captcha', 500);
	add_action('um_after_login_fields', 'um_recaptcha_add_captcha', 500);
	function um_recaptcha_add_captcha($args){
		global $um_recaptcha;
		if ( !$um_recaptcha->captcha_allowed( $args ) ) return;
		
		$your_sitekey = um_get_option('g_recaptcha_sitekey');
		
		?>
		
		<div class="g-recaptcha" data-sitekey="<?php echo $your_sitekey; ?>"></div>
		
		<?php
		
	}
	
	/***
	***	@form error handling
	***/
	add_action('um_submit_form_errors_hook', 'um_recaptcha_validate', 20);
	function um_recaptcha_validate( $args ){
		global $um_recaptcha;
		
		if ( isset($args['mode']) && !in_array( $args['mode'], array('login','register') ) ) return;
		
		if ( !$um_recaptcha->captcha_allowed( $args ) ) return;
		
		$your_secret = um_get_option('g_recaptcha_secretkey');
		$client_captcha_response = $_POST['g-recaptcha-response'];
		$user_ip = $_SERVER['REMOTE_ADDR'];
		
		$verify = wp_remote_get("https://www.google.com/recaptcha/api/siteverify?secret=$your_secret&response=$client_captcha_response&remoteip=$user_ip");
		$result = json_decode( $verify['body'] );
		
		if ( !$result->success )
			exit( wp_redirect( add_query_arg('err', 'recaptcha') ) );
	
	}