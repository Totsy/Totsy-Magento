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

class Harapartners_Affiliate_Model_Record extends Mage_Core_Model_Abstract {
    
    protected function _construct(){
        $this->_init('affiliate/record');
    }
    public function loadByAffiliateCode($affiliateCode){
    	$this->addData($this->getResource()->loadByAffiliateCode($affiliateCode));
    	return $this;
    } 
    protected function _beforeSave(){
    	if(!$this->getId() && !$this->getCreatedAt()){
    		$this->setData('created_at', now());
    	}else{
    		$this->setData('updated_at', now());
    	}
    	parent::_beforeSave();  
    }  
}