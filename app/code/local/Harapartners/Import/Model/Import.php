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
	
	const ACTION_TYPE_PROCESS_IMMEDIATELY_AND_INDEX 		= 1;
	const ACTION_TYPE_PROCESS_IMMEDIATELY 					= 2;
	const ACTION_TYPE_PENDING								= 3;
	
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
    		Mage::throwException('Invalid type for data importing, Array or Varien_Object needed.');
    	}
    	
    	//Load vendor
    	$vendor = Mage::getModel('stockhistory/vendor');
    	if(!!$data->getdata('vendor_id')){
    		$vendor->load($data->getdata('vendor_id'));
    	}elseif(!!$data->getdata('vendor_code')){
    		$vendor->loadByCode($data->getdata('vendor_code'));
    	}
    	if(!$vendor || !$vendor->getId()){
			Mage::throwException('Invalid Vendor.');
		}
		$data->setData('vendor_id', $vendor->getId());
		$data->setData('vendor_code', $vendor->getVendorCode());
		
		//Load category
		$category = Mage::getModel('catalog/category');
    	if(!!$data->getdata('category_id')){
    		$category->load($data->getdata('category_id'));
    	}
    	if(!$category || !$category->getId()){
			Mage::throwException('Invalid Category/Event.');
		}
		$data->setData('category_id', $category->getId());
    	
		//Load/Create PO
		$purchaseOrder = Mage::getModel('stockhistory/purchaseorder');
		if(!!$data->getdata('po_id')){
			$purchaseOrder->load($data->getdata('po_id'));
		}else{
			$purchaseOrder->setData('vendor_id', $vendor->getId());
			$purchaseOrder->setData('vendor_code', $vendor->getVendorCode());
			$purchaseOrder->setData('name', $data->getData('import_title'));
			$purchaseOrder->setData('category_id', $category->getId());
			$purchaseOrder->setData('comment', 'Category/Event Import ' .  date('Y-n-j H:i:s'));
			$purchaseOrder->save();
	    }
    	if(!$purchaseOrder || !$purchaseOrder->getId()){
			Mage::throwException('Invalid Purchase Order.');
		}
	    $data->setData('po_id', $purchaseOrder->getId());
    	
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

}