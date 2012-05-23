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
    const IMPORT_STATUS_UPLOADED         = 1;//'uploaded';
    const IMPORT_STATUS_PROCESSING         = 2;//'processing';
    const IMPORT_STATUS_FINALIZING         = 3;//'finalizing';
    const IMPORT_STATUS_COMPLETE         = 4;//'complete';
    const IMPORT_STATUS_ERROR             = 5;//'error';
    
    const ACTION_TYPE_PROCESS_IMMEDIATELY_AND_INDEX         = 1;
    const ACTION_TYPE_PROCESS_IMMEDIATELY                     = 2;
    const ACTION_TYPE_PENDING                                = 3;
    const ACTION_TYPE_VALIDATION_ONLY                        = 4;
    
    public function _construct() {
        parent::_construct();
        $this->_init('import/import');
    }
    
    //Note method will throw exceptions
    public function importData($dataObj){
        
        //Type casting
        if(is_array($dataObj)){
            $dataObj = new Varien_Object($dataObj);
        }
        if(!($dataObj instanceof Varien_Object)){
            Mage::throwException('Invalid type for data importing, Array or Varien_Object needed.');
        }
        
        //Load vendor
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
        
        //Load/Create PO
        $purchaseOrder = Mage::getModel('stockhistory/purchaseorder');
        if(!!$dataObj->getdata('po_id')){
            $purchaseOrder->load($dataObj->getdata('po_id'));
        }else{
            $purchaseOrderDataObj = new Varien_object();
            $purchaseOrderDataObj->setData('vendor_id', $vendor->getId());
            $purchaseOrderDataObj->setData('vendor_code', $vendor->getVendorCode());
            $purchaseOrderDataObj->setData('name', $dataObj->getData('import_title'));
            $purchaseOrderDataObj->setData('category_id', $category->getId());
            $purchaseOrderDataObj->setData('comment', 'Category/Event Import ' .  date('Y-n-j H:i:s'));
            $purchaseOrder->importData($purchaseOrderDataObj->getData())->save();
        }
        if(!$purchaseOrder || !$purchaseOrder->getId()){
            Mage::throwException('Invalid Purchase Order.');
        }
        $dataObj->setData('po_id', $purchaseOrder->getId());
        
        //Forcefully overwrite existing data, certain data may need to be removed before this step
        $this->addData($dataObj->getData());
        
        //Default values should go here
        if(!$this->getData('status')){
            $this->setData('status', self::IMPORT_STATUS_UPLOADED);
        }
        //store_id is defaulted as 0 at the DB level
        
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