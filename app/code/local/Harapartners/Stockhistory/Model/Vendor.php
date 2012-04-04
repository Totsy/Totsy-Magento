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

class Harapartners_Stockhistory_Model_Vendor extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		$this->_init('stockhistory/vendor');
	}
	
	protected function _beforeSave(){
		$now = date('Y-m-d H:i:s');
    	if(!$this->getId()){
    		$this->setData('created_at', $now);
    	}
    	$this->setData('updated_at', $now);
    	
		if(!$this->getStoreId()){
    		$this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
    	}
    	parent::_beforeSave();  
    }
    
    public function loadByCode($code, $storeId = null)
    {
    	return $this->getResource()->loadByCode($code, $storeId);
    }
    
    protected function _validateByCode($code, $storeId = null)
    {
    	return $this->getResource()->validateByCode($code, $storeId);
    }
    
	public function validateAndSave($data)
	{
    	$this->addData($data);
    	if(!$this->getVendorName() || !$this->getVendorCode() || !$this->getEmailList()){
    		throw new Exception('Required Data is missing');
    	}elseif($this->getData('id') !== $this->_validateByCode($data['vendor_code'])){
    		throw new Exception('Vendor SKU already exists, please choose another one');
    	}
    	$this->save();
    	return $this;
    }
}