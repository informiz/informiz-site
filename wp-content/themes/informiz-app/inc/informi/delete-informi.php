<?php

$userId = (string) get_current_user_id();
$informiId = get_query_var( 'informiId', '0' );
$informi = get_post($informiId);
$nonceKey = 'delete_informi_' . $userId . '_' . $informiId;

function iz_renderSuccess() { ?>
	<div class="iz_info">
	<p>Your informi was successfully deleted.</p>
	</div> <?php  
}

function iz_renderError($errMsg) { ?>
	<p class="iz_error"><?php echo esc_html( $errMsg ) ?></p> <?php
}

function iz_verifyDelete( $userId, $informiId, $informi, $nonceKey ) {
	
	if ( ! iz_authorizeDelete( $userId, $informiId, $informi ) ) {
		return false;
	} else if (! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], $nonceKey ) ) {
		iz_renderError( 'Your submission key has expired, you must refresh the page.' );
		iz_renderDeleteForm($informiId, $nonceKey);
		return FALSE;
	} else if (! iz_recaptchaValidate() ) { 
		iz_renderError( 'Please indicate that you are not a robot by solving the captcha test.' );
		iz_renderDeleteForm($informiId, $nonceKey);
		return FALSE;
	}
	return TRUE;
}

function iz_authorizeDelete( $userId, $informiId, $informi ) {
	if ( ! $informiId || ! $informi ) {
		iz_renderError( 'Informi not found.' );
		return FALSE;
	} else 	if ( $informi->post_author !== $userId ) {
		iz_renderError( 'You are not authorized to delete this informi.' );
		return FALSE;
	}
	return TRUE;
}

// TODO replace this with google recaptchalib.php
function iz_recaptchaValidate() {
	global $um_recaptcha;

	$your_secret = um_get_option('g_recaptcha_secretkey');
	$client_captcha_response = $_POST['g-recaptcha-response'];
	$user_ip = $_SERVER['REMOTE_ADDR'];
	
	$verify = wp_remote_get("https://www.google.com/recaptcha/api/siteverify?secret=$your_secret&response=$client_captcha_response&remoteip=$user_ip");
	$result = json_decode( $verify['body'] );
	
	if ( !$result->success )
		return FALSE;
		
	return TRUE;
}

function iz_renderDeleteForm($informiId, $nonceKey) {
?>
<form action="" name="submit informi" method="post" novalidate>
	Please confirm you would like to permanently delete your informi &#34;<a href="<?php echo esc_url( get_permalink($informiId) ); ?>"><?php echo get_the_title($informiId); ?></a>&#34;.
	<div class="g-recaptcha" data-sitekey="<?php echo esc_attr(um_get_option('g_recaptcha_sitekey')); ?>" style="padding:5px;"></div>
	<input type="hidden" name="submitted" id="submitted" value="true" />
	<?php 
	wp_nonce_field( $nonceKey ); 
	?>							
	<input id="iz_delete" type="submit" value="Delete">
</form>
<?php }

if ( $_POST['submitted'] ) {
	if ( iz_verifyDelete( $userId, $informiId, $informi, $nonceKey ) ) {
		require_once get_stylesheet_directory() . '/inc/informi/informi-dao.php';
		$res = iz_deleteInformi( $informiId );
		if( $res ) {
			iz_renderSuccess();
		} else {
			iz_renderError('There was a problem deleting your informi, please try again.');
			iz_renderDeleteForm($informiId, $nonceKey);
		}
	}
} else {
	if ( iz_authorizeDelete( $userId, $informiId, $informi ) ) {
		iz_renderDeleteForm($informiId, $nonceKey);
	}
}

?>