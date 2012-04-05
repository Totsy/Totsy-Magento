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

class Harapartners_Stockhistory_Model_Transaction extends Mage_Core_Model_Abstract {
	
	const STATUS_PENDING = 1;
	const STATUS_PROCESSED = 2;
	const STATUS_FAILED = 3;
	
	const ACTION_TYPE_AMENDMENT = 1;
	const ACTION_TYPE_EVENT_IMPORT = 2;
	const ACTION_TYPE_DIRECT_IMPORT = 3;
	
	public function _construct() {
		$this->_init('stockhistory/transaction');
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
    
	public function loadByProductId($productId) {
		$this->addData($this->getResource()->loadByProductId($productId));
		return $this;
	}
	
	public function validateAndSave($data){
		$this->addData($data);
		
		if(!$this->getData('vendor_id')){
			throw new Exception('Vendor ID is required.');
		}
		if(!$this->getData('po_id')){
			throw new Exception('Purchase order ID is required.');
		}
		if(!$this->getData('category_id')){
			throw new Exception('Category ID is required.');
		}
		if(!$this->getData('product_id')){
			throw new Exception('Product ID is required.');
		}
		if(!!$this->getData('unit_cost')){
			$unitCost = $this->getData('unit_cost');
			if($unitCost < 0){
				throw new Exception('Unit cost must be a non-negative number.');
			}
		}else{
			throw new Exception('Unit Cost is required.');
		}
		$this->save();
		return $this;
	}
	
}