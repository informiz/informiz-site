<?php

define("MIN_LEN", 5);
define("TITLE_LEN", 50);
define("DESC_LEN", 160);
define("PREZI_LEN", 200);
define("GAPMINDER_LEN", 700);
define("INFGRPHCS_LEN", 200);
define("TAGS_LEN", 160);
define("MIN_TAG", 2);
define("MAX_TAG", 20);


$iz_errors = [];
$iz_post = [];
$local_media_created = FALSE;

function iz_getSrcMaxLen ( $mediaType ) {
	if ('prezi' === $mediaType) {
		return PREZI_LEN;
	} else if ('gapminder' === $mediaType) {
		return GAPMINDER_LEN;
	} else if ('infographics' === $mediaType) {
		return INFGRPHCS_LEN;
	}
	return 0;
}

function iz_validateInformi($title, $desc, $lang, $mtype, $src, $cats, $tagsStr, $nonceKey) 
{
	global $iz_post;
	global $iz_errors;
	global $local_media_created;

	iz_verifyUser($nonceKey);
	
	$iz_post['title'] = iz_processTextInput($title, MIN_LEN, TITLE_LEN, 'titleError');
	$iz_post['description'] = iz_processTextInput($desc, MIN_LEN, DESC_LEN, 'descError');
	
	iz_processLanguage($lang);
	iz_processCategories ($cats);
	iz_processMedia($mtype, $src);
	
	if ($tagsStr) {
		iz_processTags ($tagsStr);
	}
	
	if ( ! empty($iz_errors) && $local_media_created ) {
		iz_unlinkMedia ( $iz_post['media_type'], $iz_post['media_src'] );
	}
	
	return empty($iz_errors);
}

function iz_saveInformi ( $id = NULL ) {
	global $iz_post;
	global $local_media_created;
	$oldSrc = NULL;
	$oldType = NULL;

	if ($id) {
		$oldSrc = get_post_meta( $id, 'iz_media_src', TRUE );
		$oldType = get_post_meta( $id, 'iz_media_type', TRUE );
	}

	$result = iz_doSaveInformi ( $id, $oldType, $oldSrc );
	if ( $result && ( ! is_wp_error($result) ) ) {
		// unlink prev media if changed
		if ( $iz_post['media_src'] !== $oldSrc ) {
			iz_unlinkMedia ( $oldType, $oldSrc );
		}
	}
	else {
		// save failed, unlink uploaded media if created
		if ( $local_media_created ) {
			iz_unlinkMedia ( $iz_post['media_type'], $iz_post['media_src'] );
		}
		// TODO consider saving a snapshot of original informi and attempting to restore it if update fails
	}
	return $result;
}

function iz_deleteInformi( $id ) {
	$res = FALSE;
	if ($id) {
		$mSrc = get_post_meta( $id, 'iz_media_src', TRUE );
		$mType = get_post_meta( $id, 'iz_media_type', TRUE );
		$res = wp_delete_post( $id );
		if( $res ) {
			iz_unlinkMedia ( $mType, $mSrc );
		}
	}
	return $res;
}

function iz_doSaveInformi ( $id, $oldType, $oldSrc ) {
	global $iz_errors;
	global $iz_post;

	$newInformi = FALSE;

	$informi = iz_constructInformi($id);
	if ($id) {
		$result = wp_update_post( $informi, TRUE );
		if (! $result) { 
			$iz_errors['genError'] = 'Informi submission has failed due to an unknown error'; // TODO log
			return $result; 
		}
	} else {
		$result = wp_insert_post( $informi, TRUE );
		if ( is_wp_error($result) ) {
			if ( count( $result->get_error_messages() ) > 0 ) {
				$iz_errors['genError'] = 'Informi update has failed due to error(s): ' . implode(", ",$result->get_error_messages());
			} else {
				$iz_errors['genError'] = 'Informi update has failed due to an unknown error';
			}
			return $result;
		}
		$id = $result;
		$newInformi = TRUE;
	}

	if (! iz_saveInformiMeta( $id, $iz_post['media_type'], $iz_post['media_src'], $oldSrc, $oldType ) ) {
		if ($newInformi) {
			wp_delete_post( $id, TRUE );
		} 
		// else { updated informi may be in an inconsistent state... } 
		return FALSE; 
	}

	if ( ! iz_saveInformiLang ( $id, $iz_post['lang'] )) {
		if ($newInformi) {
			wp_delete_post( $id, TRUE );
		} 
		// else { updated informi may be in an inconsistent state... }
		return FALSE;
	}
	return $id;
}

function iz_constructInformi( $id = NULL ) {
	global $iz_post;
	
	$theTitle = wp_strip_all_tags( $iz_post['title'] );
	$informi = array(
		'post_content'   => $iz_post['description'],
		'post_name'      => sanitize_title( $theTitle ),
		'post_title'     => $theTitle,
		'post_status'    => 'publish',
		'post_type'      => 'informi',
		'post_excerpt'   => $iz_post['description'],
		'comment_status' => 'open',
		'post_category'  => $iz_post['categories'], 
		'tags_input'     => $iz_post['tags']
	);
	if ( $id ) {
		$informi['ID'] = $id;
	}
	return $informi;
}

function iz_saveInformiMeta ( $id, $mtype, $msrc, $oldSrc, $oldType ) {
	global $iz_errors;

	if ( ! $mtype || ! $msrc ) {
		$iz_errors['mediaError'] = 'Cannot save empty value as informi source';
		return FALSE;
	}

	if ( $oldType === $mtype && $oldSrc === $msrc ) {
		return TRUE;
	}

	if (! iz_updateMeta( $id, 'iz_media_type', $mtype, $oldType ) ) {
		$iz_errors['mediaError'] = 'Failed to save media type';
		return FALSE;
	}

	if (! iz_updateMeta ( $id, 'iz_media_src', $msrc, $oldSrc ) ) {
		iz_updateMeta( $id, 'iz_media_type', $oldType, $mtype ); // best effort - try to restore prev value
		$iz_errors['mediaError'] = 'Failed to save informi source';
		return FALSE;
	}
	
	return TRUE;
}

function iz_updateMeta ( $id, $metaKey, $metaVal, $oldVal ) {
	if ($oldVal === $metaVal) return TRUE;
	return update_post_meta($id, $metaKey, $metaVal); 
}

function iz_unlinkMedia ( $mtype, $msrc ) {
	if( 'infographics' === $mtype ) {
		if( ! $msrc ) {
			return; // TODO should never happen, log
		} 
		$upload_dir = wp_upload_dir();
		$fullpath = $upload_dir['basedir'] . '/infographics/' . $msrc;
		if( file_exists($fullpath) ) {
			if( ! unlink($fullpath) ) {
				// TODO log
			}
		}
	}
}

function iz_saveInformiLang ( $id, $lang ) {
	global $iz_errors;
	if ( ! iz_ensureLangTermExists ( $lang ) ) {
		return FALSE;
	}
	$res = wp_set_object_terms( $id, $lang, 'iz_lang' );
	if (! is_array($res) || empty($res) ) {
		if ( is_wp_error($res) ) {
			if ( count( $res->get_error_messages() ) > 0 ) {
				$iz_errors['langError'] = 'Failed to save informi language due to error(s): ' . implode(", ",$res->get_error_messages());
			} else {
				$iz_errors['langError'] = 'Failed to save informi language due to an unknown error';
			}
		} else if (is_string($res)){
			$iz_errors['langError'] = 'Failed to save offending term ' . $iz_post['lang'] .' as informi language';
		} else {
			$iz_errors['langError'] = 'Failed to save informi language';
		}
		return FALSE;
	}
	return TRUE;
}

function iz_ensureLangTermExists ( $lang ) {
	global $iz_errors;
	
	if ( ! $lang ) {
		$iz_errors['langError'] = 'Cannot save empty language';
		return FALSE;
	}
	$term = term_exists($lang, 'iz_lang');
	if ($term === 0 || $term === null) {
		$result = wp_insert_term( $lang, 'iz_lang', $args = array() );
		if ( is_wp_error($result) ) {
			if ( count( $result->get_error_messages() ) > 0 ) {
				$iz_errors['langError'] = 'Failed to add language due to error(s): ' . implode(", ",$result->get_error_messages());
			} else {
				$iz_errors['langError'] = 'Failed to add language due to an unknown error';
			}
			return FALSE;
		}
	}
	return TRUE;
}

function iz_processMedia($mtype, $src) {
	global $iz_errors;
	global $iz_post;

	if ( (! $mtype) || (! $src) ) {
		$iz_errors['mediaError'] = 'Please select the informi\'s media and supply a link to the source file';
		return '';
	}
	global $wpdb;
	$media = $wpdb->get_row( $wpdb->prepare("SELECT submit_regexp, embed_template, example FROM informi_media where name = %s", $mtype));
	if (! $media  || ! $media->submit_regexp) {
		$iz_errors['mediaError'] = 'The media type you selected - ' . $mtype . ' - is invalid';
		return '';
	} 
	if ($mtype === 'infographics') {
		$id = iz_processInfographics($media, $src);
		if (! $id ) {
			return '';
		}
	} else {
		preg_match($media->submit_regexp, $src, $matches);
		if (! $matches || ! $matches[1] ) {
			$iz_errors['mediaError'] = 'Your informi source must be of the form ' . $media->example;
			return '';
		}
		$id = $matches[1];
	}
	$url = sprintf($media->embed_template, $id);
	$resp = wp_remote_retrieve_response_code(wp_remote_get($url));
	if( $resp != 200) {
		$iz_errors['mediaError'] = 'The informi source is unavailable, returned HTTP code is ' . $resp;
		return '';
	}

	$iz_post['media_type'] = $mtype;
	$iz_post['media_src'] = $id;
}

function iz_processInfographics($media, $mSrc) {
	global $iz_errors;

	if ( is_string( $mSrc ) ) { // url
		// check if the file is already in the local library.
		$local_regexp = iz_getLocalInfgrphcsRegex($media->submit_regexp);
		preg_match($local_regexp, $mSrc, $localMatch);
		if ( $localMatch && $localMatch[1] ) {
			return $localMatch[1];
		}
	}
	if ( ! iz_verifyInfgrphcsSize ( $mSrc, $iz_errors ) ) {
		return '';
	}
	return iz_copyInfographics( $media, $mSrc, $iz_errors );
}

function iz_verifyInfgrphcsSize ( $mSrc, &$errors ) {
	$max_size = 2000000; // 2MB should be enough for infographics

	if ( is_string( $mSrc ) ) { // remote url
		$ch = curl_init($mSrc);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);

		$data = curl_exec($ch);
		$size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

		curl_close($ch);
		if ( $size <= 0 || $size > $max_size ) {
			$errors['mediaError'] = 'File should not be empty and no bigger than 2MB.';
			return FALSE;
		}
	} else { // upload
		if ( $mSrc['error'] !== UPLOAD_ERR_OK ) {
			$errors['mediaError'] = 'Failed to upload selected file.';
		}
		$size = $mSrc['size'];
		if ( $size <= 0 || $size > $max_size ) {
			$errors['mediaError'] = 'File should not be empty and no bigger than 2MB.';
			return FALSE;
		} 
	}
	return TRUE;
}

function iz_copyInfographics( $media, $mSrc, &$errors ) {
	global $local_media_created;
	
	if ( is_string( $mSrc ) ) { // url
		preg_match($media->submit_regexp, $mSrc, $matches);
		if (! $matches || ! $matches[1] ) {
			$errors['mediaError'] = 'Your informi source must be of the form ' . $media->example;
			return '';
		}
		$ext = $matches[3];
		$src = $mSrc;
		unset($matches);
	} else { // file upload
		$path = $mSrc['name'];
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		$src = $mSrc['tmp_name'];
	}
	$filename = iz_getUploadFilename($src, $ext, $errors);
	$uploadDir = iz_getUserUploadDir($errors);
	if ( ! $filename || ! $uploadDir ) {
		return '';
	}
	$target = $uploadDir . '/' . $filename;
	$local_regexp = iz_getTargetInfgrphcsRegex($media->submit_regexp);
	preg_match($local_regexp, $target, $matches); // matches[1] will contain the source-id for the informi
	if (! $matches || ! $matches[1] ) {
		$errors['mediaError'] = 'Internal error: target file path '. $target .' does not match expected format '. $local_regexp;
		return '';
	}
	
	if ( iz_saveFileToTarget($src, $ext, $target, $errors) ) {
		$local_media_created = TRUE;
		return $matches[1];
	} else {
		return '';
	}
}

function iz_getLocalInfgrphcsRegex($submitRegex) {
	// submit_regexp = (([A-Za-z0-9_\-\~]+)\.([jJ][pP][eE]?[gG]|[pP][nN][gG]|[gG][iI][fF]))$
	// match[1] will capture the part usedId/filename.ext
	return '#http://informiz.org/wp-content/uploads/infographics/([0-9]+/' . substr($submitRegex, 1, -1) . ')#';
}

function iz_getTargetInfgrphcsRegex($submitRegex) {
	// submit_regexp = (([A-Za-z0-9_\-\~]+)\.([jJ][pP][eE]?[gG]|[pP][nN][gG]|[gG][iI][fF]))$
	// match[1] will capture the part usedId/filename.ext
	return '#infographics/([0-9]+/' . substr($submitRegex, 1, -1) . ')#';
}

function iz_getUserUploadDir(&$errors) {
	$upload_dir = wp_upload_dir(); 
	$user_ID = (string) get_current_user_id();
	if ( ! $user_ID ) {
		// TODO: should not get here if no user, log
		$errors['mediaError'] = 'You must be logged in to upload a file.';
		return '';
	}
	
	$user_dirname = $upload_dir['basedir'] . '/infographics/' . $user_ID;
	if( ! file_exists( $user_dirname ) )
		if ( ! wp_mkdir_p( $user_dirname ) ) {
			$errors['mediaError'] = 'Internal error: failed to create user directory.';
			return '';
		}
	return $user_dirname;
}

function iz_getUploadFilename($src, $ext, &$errors) {
	$filename = sha1_file($src);
	if ( ! $filename ) {
		$errors['mediaError'] = 'Internal error: failed to generate local file-name.';
		return '';
	}
	return $filename . '.' . $ext;
}

function iz_saveFileToTarget($src, $ext, $target, &$errors) {
	$im = NULL;
	$saved = FALSE;

	switch (strtolower($ext)) {
		case 'jpg':
		case 'jpeg':
			$im = imagecreatefromjpeg($src);
			if ( $im ) $saved = imagejpeg( $im, $target, 100 );
			break;
		case 'png':
			$im = imagecreatefrompng($src);
			if ( $im ) $saved = imagepng( $im, $target, 0 );
			break;
		case 'gif':
			$im = imagecreatefromgif($src); 
			if ( $im ) $saved = imagegif( $im, $target );
			break;
		default:
			// TODO - this should not happen, log
			$errors['mediaError'] = 'Internal error: failed to identify image format';
			return FALSE;
	}
	if (! $im ) {
		$errors['mediaError'] = 'Failed to create a local copy of the image file. Please make sure the source you supplied is a valid image.';
		return FALSE;
	}
	imagedestroy( $im );
	
	if (! $saved ) {
		$errors['mediaError'] = 'Internal error: failed to save a local copy of the image file.';
		return FALSE;
	}

	return TRUE;
}

function iz_processCategories ($cats) {
	global $iz_errors;
	global $iz_post;
	if (! $cats ||  count($cats) == 0 ) {
		$iz_errors['catError'] = 'Please select at least one category';
		return;
	} else {
		$args = array( 'hide_empty' => 0, 'include' => implode(',', $cats) ); 
		$categories = get_categories( $args );
		if (count($categories) < count($cats)) {
			$iz_errors['catError'] = 'One or more of the selected categories does not exist';
			return;
		}
		$iz_post['categories'] =  $cats;
	}
}

function iz_processLanguage ($lang) {
	global $iz_errors;
	global $iz_post;
	
	if (! $lang ) {
		$iz_errors['langError'] = 'Please select a language';
		return;
	} else {
		global $wpdb;
		$code = $wpdb->get_var( $wpdb->prepare( 'SELECT code FROM languages WHERE code = %s', $lang ) );
		if (! $code ) {
			$iz_errors['langError'] = 'Selected language not found';
			return;
		}
		$iz_post['lang'] = $lang;
	}
}

function iz_processTextInput($input, $minLen, $maxLen, $key, $req = TRUE) {
	global $iz_errors;
	if (! $input ) {
		if ($req) {
			$iz_errors[$key] = 'Input is required';
		}
		return '';
	}
	$effLen = mb_strlen(wp_strip_all_tags( $input ));
	if( $effLen < $minLen ) {
		$iz_errors[$key] = 'Input must contain at least ' . $minLen . ' printable characters.';
	}
	else if( $effLen > $maxLen ) {
		$iz_errors[$key] = 'Input must contain no more than ' . $maxLen . ' printable characters.';
	} 
	return mb_substr($input, 0, $maxLen);
}


function iz_processTags ($tags) {
	global $iz_errors;
	global $iz_post;
	if (! iz_processTextInput($tags, 0, TAGS_LEN, 'tagError')) {
		return;
	}
	$tagsArr = explode(',', $tags);
	array_walk($tagsArr, 'iz_processTag');
	if ( $iz_errors['tagError'] ) {
		$iz_errors['tagError'] = 'Each tag must be between ' . MIN_TAG . ' and ' . MAX_TAG . ' characters long';
		return;
	}
	$iz_post['tags'] =  $tagsArr;
}

function iz_processTag(&$tag, $index) {
    $tag = iz_processTextInput($tag, MIN_TAG, MAX_TAG, 'tagError', FALSE);
}
?>