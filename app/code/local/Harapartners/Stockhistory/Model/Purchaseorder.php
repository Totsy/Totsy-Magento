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

class Harapartners_Stockhistory_Model_Purchaseorder extends Mage_Core_Model_Abstract {
	
	const STATUS_OPEN = 1;
	const STATUS_ON_HOLD = 2;
	const STATUS_SUBMITTED = 3;
	const STATUS_COMPLETE = 4;
	const STATUS_CANCELLED = 5;
	
	protected $_vendorObj;
	
	public function _construct() {
		$this->_init('stockhistory/purchaseorder');
	}
	
	protected function _beforeSave(){
		if(!$this->getId()){
    		$this->setData('created_at', now());
    	}
    	$this->setData('updated_at', now());
    	
		if(!$this->getStoreId()){
    		$this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
    	}
    	parent::_beforeSave();  
	}
	
	public function loadByVendorId($vendorId, $storeId = null){
		$this->addData($this->getResource()->loadByVendorId($vendorId, $storeId));
		return $this;
	}
	
	public function getVendorObj() {
		if(!$this->_vendorObj){
			$this->_vendorObj = Mage::getModel('stockhistory/vendor')->load($this->getData('vendor_id'));
		}
		return $this->_vendorObj;
	}
	
	public function generatePoNumber(){
		return strtoupper(substr($this->getVendorObj()->getVendorCode(), 0, 3)) . strtotime($this->getCreatedAt()); 
	}
	
	public function importData($dataObj){
		
		//Type casting
    	if(is_array($dataObj)){
    		$dataObj = new Varien_Object($dataObj);
    	}
    	if(!($dataObj instanceof Varien_Object)){
    		Mage::throwException('Invalid data type, Array or Varien_Object needed.');
    	}
		
		$vendor = Mage::getModel('stockhistory/vendor');
    	if(!!$dataObj->getdata('vendor_id')){
    		$vendor->load($dataObj->getdata('vendor_id'));
    	}elseif(!!$dataObj->getdata('vendor_code')){
    		$vendor->loadByCode($dataObj->getdata('vendor_code'));
    	}
    	if(!$vendor || !$vendor->getId()){
			Mage::throwException('Invalid Vendor.');
		}
		$dataObj->setData('vendor_id', $vendor->getId());
		$dataObj->setData('vendor_code', $vendor->getVendorCode());
		
		//Load category
		$category = Mage::getModel('catalog/category');
    	if(!!$dataObj->getdata('category_id')){
    		$category->load($dataObj->getdata('category_id'));
    	}
    	if(!$category || !$category->getId()){
			Mage::throwException('Invalid Category/Event.');
		}
		$dataObj->setData('category_id', $category->getId());
		
		if(!$dataObj->getData('status')){
			$dataObj->setData('status', self::STATUS_OPEN);
		}
		
		$this->addData($dataObj->getData());

		return $this;
	}
	
}