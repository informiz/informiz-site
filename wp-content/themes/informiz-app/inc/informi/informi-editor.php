<?php

require_once get_stylesheet_directory() . '/inc/informi/informi-dao.php';

function iz_formSubmitted() {
	return $_POST['submitted'];
}

$iz_saved = FALSE;
function iz_informiSaved() {
	return $GLOBALS['iz_saved'];
}

function iz_submitInformi( $nonceKey, $informiId = NULL ) {
	if ( iz_formSubmitted() ) {
		$mType = $_POST['iz_media'];
		$mSrc = iz_getInformiSource ( $mType );
		
		if ( ! mSrc ) return FALSE;
		
		$isValid = iz_validateInformi($_POST['iz_title'], $_POST['iz_desc'], $_POST['iz_lang'], $mType, $mSrc, $_POST['iz_cats'], $_POST['iz_tags'], $nonceKey) ;
		if ( $isValid ) {
			$result = iz_saveInformi( $informiId );
			if ( $result && ! is_wp_error($result) ) {
				$GLOBALS['iz_saved'] = TRUE;
				return $result;
			}
		}
	}
	return FALSE;
}

function iz_getInformiSource ( $mType ) {
	if ( 'infographics' == $mType ) {
		$opt = $_POST['infgrphcs'];
		if ( 'infgrphcs_up' == $opt ) {
			return $_FILES['infgrphcs_file'];
		}
	}
	return $_POST[$mType];
}

function iz_getTitleAtrr() {
	if (iz_informiSaved()) {
		return '';
	}
	return iz_trimInput(iz_getTitle(), TITLE_LEN);
}

function iz_getTitlePH() {
	if (iz_informiSaved()) {
		return iz_trimInput(iz_getTitle(), TITLE_LEN);
	}
	return '';
}

function iz_getDescAtrr() {
	if (iz_informiSaved()) {
		return '';
	}
	return iz_trimInput(iz_getDescription(), DESC_LEN);
}

function iz_getDescPH() {
	if (iz_informiSaved()) {
		return iz_trimInput(iz_getDescription(), DESC_LEN);
	}
	return '';
}

function iz_getTagsAtrr() {
	if (iz_informiSaved()) {
		return '';
	}
	return iz_trimInput(iz_getTags(), TAGS_LEN);
}

function iz_getTagsPH() {
	if (iz_informiSaved()) {
		return iz_trimInput(iz_getTags(), TAGS_LEN);
	}
	return 'Comma, separated, list of, tags';
}

function iz_getSrcAtrr( $id ) {
	if (iz_informiSaved()) {
		return '';
	}
	return iz_sanitizeSrc( $id );
}

function iz_getSrcPH( $id, $default='' ) {
	if (iz_informiSaved()) {
		return iz_sanitizeSrc( $id );
	}
	return $default;
}

function iz_sanitizeSrc( $id ) {
	$maxLen = iz_getSrcMaxLen($id);
	$src = iz_getSource( $id );
	if ( $maxLen && $src ) {
		return iz_trimInput($src, $maxLen);
	} else {
		return '';
	}
}

function iz_trimInput($input, $len) 
{
	// TODO make sure it is clear why input has been cut, especially in URLs
	if (! $input) {
		return '';
	}
	return mb_substr($input, 0, $len);
}

function iz_getMediaOptions() {
	global $wpdb;
	return $wpdb->get_results("SELECT ID, name, submit_regexp, example FROM informi_media");
}

function iz_getLanguages() {
	global $wpdb;
	return $wpdb->get_results("SELECT code, lang, display FROM languages");
}

function iz_getCategories() {
	$args = array( 'hide_empty' => 0, 'exclude' => '1,6,8'); 
	return get_categories( $args );
}

function iz_verifyUser($nonceKey) {
	global $iz_errors;
	if (! is_user_logged_in() ) { // TODO shouldn't happen!!! check session bug!!!!
		$iz_errors['genError'] = 'You must be logged in to submit an informi.';
		return;
	}
	if (! iz_recaptchaValidate() ) { 
		$iz_errors['genError'] = 'Please indicate that you are not a robot by solving the captcha test.';
		return;
	}
	if (! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], $nonceKey ) ) 
	{
		$iz_errors['genError'] = 'Your submission key has expired, you must reload the form';
		return;
	}
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

function iz_renderMediaSrcWidget($media) {
	switch ($media->name) {
		case 'prezi':
		case 'gapminder':
			echo '<div class="iz_mediaOption" name=' . esc_attr($media->name) . ' style="display:none;margin-left:10px;"><div class="iz_helpIcon" title="Link to the source file used as an informi">?</div>';
			echo '<label for=' . esc_attr($media->name) . '>Full url to ' . esc_html($media->name) . '</label><span>  *</span>';
			echo '<div style="display:block"><input id="' . esc_attr($media->name) . '" name="' . esc_attr($media->name) . '" value="' . esc_attr(iz_getSrcAtrr($media->name)) . '" type="url" maxlength="600" pattern="' . esc_attr( substr($media->submit_regexp,1,-1) ) . '" placeholder="' . esc_attr(iz_getSrcPH($media->name, $media->example)) . '"/></div></div>';
	
			break;
		case 'infographics':
			$checked1 = 'checked';
			$checked2 = '';
			$display1 = 'display:block;';
			$display2 = 'display:none;';
			if (isset($_POST['infgrphcs']) && $_POST['infgrphcs'] == 'infgrphcs_up') {
				$checked1 = '';
				$checked2 = 'checked';
				$display1 = 'display:none;';
				$display2 = 'display:block;';
			}
			echo '<div class="iz_mediaOption" name=' . esc_attr($media->name) . ' style="display:none;margin-left:10px;margin-bottom:10px;">'; 
			echo'<div class="iz_helpIcon" title="Infographic source file in jpeg/jpg/gif/png format">?</div>';
			echo '<label for="infgrphcs">Image file</label><span>  *</span>';
			echo '<div style="margin-left:10px;"><input type="radio" id="infgrphcs" name="infgrphcs" value="infgrphcs_url" style="margin:5px;" ' . esc_attr( $checked1 ) . '>URL</input>';
			echo '<input type="radio" id="infgrphcs" name="infgrphcs" value="infgrphcs_up" style="margin:5px 5px 5px 10px;" ' . esc_attr( $checked2 ) . '>Upload</input>';
			echo '<div class="iz_infgrphcsOpt" id="infgrphcs_url" style="' . esc_attr($display1) . '"><input name="infographics" id="infographics" value="' . esc_attr(iz_getSrcAtrr($media->name)) . '" type="url" maxlength="600" pattern="^http[s]?://.{1,100}/' . esc_attr( substr($media->submit_regexp,1,-1) ) . '" placeholder="' . esc_attr(iz_getSrcPH($media->name, $media->example)) . '"/></div>';
			echo '<div class="iz_infgrphcsOpt" id="infgrphcs_up" style="' . esc_attr($display2) . '"><input type="file" name="infgrphcs_file" id="infgrphcs_file" /></div></div></div>';
			break;
	}
}


function iz_renderForm($nonceKey) { 
	global $iz_errors;
?>
<form action="<?php echo esc_url( iz_getFormAction() ); ?>" name="submit informi" method="post" enctype="multipart/form-data" novalidate>
	<fieldset>
		<legend>Informi details</legend>
		<div class="iz_helpIcon" title="Informi name, maximum <?php echo esc_attr(TITLE_LEN); ?> charachters">?</div>
		<label for="iz_title">Title</label><span>  *</span>
		<div style="display:block"><input type="text" id="iz_title" name="iz_title" value="<?php echo esc_attr(iz_getTitleAtrr()) ?>" size="40" maxlength="<?php echo esc_attr(TITLE_LEN); ?>" placeholder="<?php echo esc_attr(iz_getTitlePH()) ?>" required></div>
		<?php if($iz_errors['titleError']) { ?>
			<div><div class="iz_error"><? echo esc_html($iz_errors['titleError']); ?></div></div>
		<?php } ?>
		<div class="iz_helpIcon" title="Will appear as post content before the informi, maximum <?php echo esc_attr(DESC_LEN); ?> charachters">?</div>
		<label for="iz_desc">Description</label>
		<div style="display:block"><textarea id="iz_desc" name="iz_desc" rows="4" cols="40" size="40" maxlength="<?php echo esc_attr(DESC_LEN); ?>" placeholder="<?php echo esc_attr(iz_getDescPH()) ?>"><?php echo stripslashes(esc_textarea(iz_getDescAtrr())) ?></textarea></div>
		<?php if($iz_errors['descError']) { ?>
			<div><div class="iz_error"><? echo esc_html($iz_errors['descError']); ?></div></div> 
		<?php } ?>							
	</fieldset>
	<fieldset>
		<legend>Informi media</legend>
		<div class="iz_mediaHelp">?</div>
		<label for="iz_media">Media type</label><span>  *</span>
		<div style="display:block"><select id="iz_media" name="iz_media">
			<?php
			$iz_media = iz_selectedMedia();
			$mediaOpts = iz_getMediaOptions();
			foreach($mediaOpts as $media) {
				$val = esc_attr($media->name);
				$selected = '';
				if( $iz_media === $val ) $selected = ' selected=&#34;selected&#34;'; 
				echo '<option value=' . esc_attr($val) . esc_attr($selected) . '>' . esc_html($val) . '</option>';
			}
			?>
		</select></div>
		<?php
		foreach($mediaOpts as $media) {
			iz_renderMediaSrcWidget($media);
		}
		?>
		<?php if($iz_errors['mediaError']) { ?>
			<div><div class="iz_error"><? echo esc_html($iz_errors['mediaError']); ?></div></div> 
		<?php } ?>							
	</fieldset>
	<fieldset>
		<legend>Metadata</legend>
		<div class="iz_helpIcon" title="The informi's language">?</div>
		<label for="iz_lang">Language</label><span>  *</span>
		<div style="display:block"><select id="iz_lang" name="iz_lang">
			<option value=""> --- </option>
			<?php
			$languages = iz_getLanguages();
			$selLang = iz_selectedLanguage();
			foreach($languages as $language) {
				$text = $language->lang;
				$val = $language->code;
				$selected = '';
				if($selLang === $val) $selected = ' selected'; 
				if ($text !== $language->display) {
					$text .= ' (' . $language->display . ')';
				}
				echo '<option value=' . esc_attr($val . $selected) . '>' . esc_html($text) . '</option>';
			}
			?>
		</select></div>
		<?php if($iz_errors['langError']) { ?>
			<div><div class="iz_error"><? echo esc_html($iz_errors['langError']); ?></div></div> 
		<?php } ?>							
		<div class="iz_helpIcon" title="Categories allow scholars in those fields rate your informi, and help users search for it">?</div>
		<label for="cats">Categories</label>
		<div style="display:block"><select id="iz_cats[]" name="iz_cats[]" multiple>
			<?php
			$selCats = iz_selectedCategories();
			$categories = iz_getCategories();
			foreach ($categories as $category) {
				$selected = '';
				$id = $category->cat_ID;
				if($selCats && in_array((string)$id, $selCats)) $selected = ' selected';
				echo '<option value=' . esc_attr($id) . $selected . '>' . esc_html($category->cat_name) . '</option>';
			}
			?>
		</select></div>
		<?php if($iz_errors['catError']) { ?>
			<div><div class="iz_error"><? echo esc_html($iz_errors['catError']); ?></div></div> 
		<?php } ?>							
		<div class="iz_helpIcon" title="Tags help users search for informis about subjects they're interested in">?</div>
		<label for="tags">Tags</label>
		<div style="display:block"><textarea id="iz_tags" name="iz_tags" rows="4" cols="40" size="40" maxlength="160" placeholder="<?php echo esc_attr(iz_getTagsPH()); ?>"><?php echo stripslashes(esc_textarea(iz_getTagsAtrr())); ?></textarea></div>
		<?php if($iz_errors['tagError']) { ?>
			<div><div class="iz_error"><? echo esc_html($iz_errors['tagError']); ?></div></div> 
		<?php } ?>							
	</fieldset>
	<fieldset>
	<div class="g-recaptcha" data-sitekey="<?php echo esc_attr(um_get_option('g_recaptcha_sitekey')); ?>" style="padding-bottom:5px;"></div>
	<input type="hidden" name="submitted" id="submitted" value="true" />
	<?php 
	wp_nonce_field( $nonceKey ); 
	if($iz_errors['genError']) { ?>
		<div><div class="iz_error"><? echo esc_html($iz_errors['genError']); ?></div></div> 
	<?php } ?>							
	</fieldset>
	<input id="test" type="submit" value="Submit">
</form>

<div id="iz-media_tip" style="display:none">
	<p>Currently supported media-types are Infographics, <a href="https://prezi.com/">Prezi</a> and <a href="http://www.gapminder.org/">Gapminder</a>.</p>
	<p>Need another media-type? Please suggest it through our <a href="http://informiz.org/contact/">contact page</a>!</p>
</div>
<?php } ?>