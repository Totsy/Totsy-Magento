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

class Harapartners_EmailFactory_Model_Sailthruconfig extends Mage_Core_Model_Abstract {
	
	protected $_handle;
	
	protected function _construct() {
		$this->_init("emailfactory/sailthruconfig");
		include_once("sailthru_api/Sailthru_Client_Exception.php");
		include_once("sailthru_api/Sailthru_Client.php");
		include_once("sailthru_api/Sailthru_Util.php");  
		$this->_handle = new Sailthru_Client(
				Mage::getStoreConfig('sailthru_options/api/sailthru_api_key'), 
				Mage::getStoreConfig('sailthru_options/api/sailthru_api_secret')
		);
	}
	
	public function getHandle() {
		return $this->_handle;
	}
	
}