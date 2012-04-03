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

class Harapartners_Stockhistory_Model_Purchaseorder extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		$this->_init('stockhistory/purchaseorder');
	}
	
	protected function _beforeSave()
	{
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
	
	public function loadByVendorId($vendorId, $storeId = null)
	{
		return $this->getResource()->loadByVendorId($vendorId, $storeId);
	}
	
	protected function validateByVendorId($vendorId, $storeId = null)
	{
		return $this->getResource()->validateByVendorId($vendorId, $storeId);
	}
	
	public function validateAndSave($data)
	{
		$this->addData($data);
		if(!$this->getVendorId()){
			throw new Exception('Vendor ID is missing');
		}elseif(!$this->validateByVendorId($data['vendor_id'])){
			throw new Exception('Vendor ID does not exist');
		}	
		$this->save();
		return $this;
	}
}