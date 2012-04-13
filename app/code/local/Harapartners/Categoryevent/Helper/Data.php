<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */
 
class Harapartners_Categoryevent_Helper_Data extends Mage_Core_Helper_Abstract{	

	const ADMIN_CATEGORY_PREVIEW = 'admin_categoryevent_preview';
	const ADMIN_CATEGORY_PREVIEW_CODE = '0129384723847192340124';
	const ADMIN_CATEGORY_PREVIEW_KEY = 'admin preview passport';
	
	function getPreviewCookieName() {
		return self::ADMIN_CATEGORY_PREVIEW;
	}
	
	function getPreviewCookieEncryptedCode() {
		$code = self::ADMIN_CATEGORY_PREVIEW_CODE;
		$key = self::ADMIN_CATEGORY_PREVIEW_KEY;
		$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $code, MCRYPT_MODE_CBC, md5(md5($key))));
		return $encrypted;
	}
	
	function getPreviewCookieDecryptedCode( $encrypted ) {
		$key = self::ADMIN_CATEGORY_PREVIEW_KEY;
		$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
		return $decrypted;
	}
	
}