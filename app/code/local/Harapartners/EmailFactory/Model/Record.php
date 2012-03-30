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

class Harapartners_EmailFactory_Model_Record extends Mage_Core_Model_Abstract {

    
	const SAILTHRU_API_STATUS_UNCHECK = 0;
	const SAILTHRU_API_STATUS_CHECK = 1;
	const SAILTHRU_API_STATUS_UNKNOWN = 2;
	
	
	protected function _beforeSave(){
    	if(!$this->getId()){
    		$this->setData('created_at', now());
    	}
    	$this->setData('updated_at', now());
    	parent::_beforeSave();  
    }
    
	protected function _construct(){
        $this->_init('emailfactory/record');
    }
}