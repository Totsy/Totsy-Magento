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

class Harapartners_Import_Model_Import extends Mage_Core_Model_Abstract {
	//Harapartners_Import_Model_Import::IMPORT_STATUS_
	const IMPORT_STATUS_UPLOADED 		= 1;//'uploaded';
	const IMPORT_STATUS_PROCESSING 		= 2;//'processing';
	const IMPORT_STATUS_FINALIZING 		= 3;//'finalizing';
	const IMPORT_STATUS_COMPLETE 		= 4;//'complete';
	const IMPORT_STATUS_ERROR 			= 5;//'error';
	
	const ACTION_TYPE_PROCESS_IMMEDIATELY 		= '1';
	const ACTION_TYPE_PENDING					= '2';
	
    public function _construct() {
        parent::_construct();
        $this->_init('import/import');
    }
    
	//Note method will throw exceptions
    public function importDataWithValidation($data){
    	
    	//Type casting
    	if(is_array($data)){
    		$data = new Varien_Object($data);
    	}
    	if(!($data instanceof Varien_Object)){
    		throw new Exception('Invalid type for data importing, Array or Varien_Object needed.');
    	}
    	
    	//Load vendor
    	
    	if(!$data->getdata('po_id')){
			$newPurchaseOrder = Mage::getModel('stockhistory/purchaseorder');
			$newPurchaseOrder->setData('vendor_id', $data->getdata('vendor_id'));
			$newPurchaseOrder->setData('name', 'Category Product Import');
			$newPurchaseOrder->setData('comment', date('Y-n-j H:i:s'));
			$newPurchaseOrder->save();
			$data->setData('po_id', $newPurchaseOrder->getId());
	    }
    	
    	
    	//Forcefully overwrite existing data, certain data may need to be removed before this step
    	$this->addData($data->getData());
    	
    	//Default values should go here
    	if(!$this->getData('status')){
    		$this->setData('status', self::IMPORT_STATUS_UPLOADED);
    	}
    	//store_id is defaulted as 0 at the DB level
    	
		$this->validate();
		return $this;
    }
    
    public function validate(){
    	//Note some of the ID field are validated at the DB level by foreign key
    	if(!$this->getData('vendor_id')){
    		throw new Exception('Invalid vendor.');
    	}
    	
    	if(!$this->getData('vendor_code')){
    		throw new Exception('Vendor code is required.');
    	}
    	
    	if(!$this->getData('po_id')){
    		throw new Exception('Purchase Order ID is invalid.');
    	}
    	
    	if(!$this->getData('category_id')){
    		throw new Exception('Category/Event ID is invalid.');
    	}
    	
    	return $this;
    }
    
    protected function _beforeSave(){
    	parent::_beforeSave();
    	//For new object which does not specify 'created_at'
    	if(!$this->getId() && !$this->getData('created_at')){
    		$this->setData('created_at', now());
    	}
    	//Always specify 'updated_at'
    	$this->setData('updated_at', now());
    	$this->validate(); //Errors will be thrown as exceptions
    }
//    
//    
//    
//    
// 	//This is for updating 'created_at' and 'updated_at'
//    protected function _beforeSave(){
//    	//Magento Standard, always assume UTC timezone
//    	if(!$this->getId()){
//    		$this->setData('created_time', now());
//    	}
//    	$this->setData('update_time', now());
//    	parent::_beforeSave();
//    }
}