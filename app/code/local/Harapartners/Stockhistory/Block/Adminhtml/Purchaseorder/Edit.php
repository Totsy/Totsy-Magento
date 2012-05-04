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

class Harapartners_Stockhistory_Block_Adminhtml_Purchaseorder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
    
    public function __construct() {
        $dataObject = new Varien_Object(Mage::registry('stockhistory_po_data'));
        
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'stockhistory';
        $this->_controller = 'adminhtml_purchaseorder';

        $this->_removeButton('add');
        
        if(!!$dataObject->getId()){
            $poObject = Mage::getModel('stockhistory/purchaseorder')->load($dataObject->getId());
            if($poObject->getStatus() == Harapartners_Stockhistory_Model_Purchaseorder::STATUS_OPEN){
                $this->_addButton('transaction_add', array(
                    'label'     => Mage::helper('stockhistory')->__('Create Amendment'),
                    'onclick'   => 'setLocation(\'' . $this->getCreateAmendmentUrl() .'\')',
                    'class'        => 'add',
                  ));
            }
            
              $this->_addButton('generate_report', array(
                  'label'        =>    Mage::helper('stockhistory')->__('Generate Report'),
                  'onclick'    => 'setLocation(\'' . $this->getReportUrl() .'\')',
              ));
        }
    }
    
    public function getHeaderText() {
        return Mage::helper('stockhistory')->__('Purchase Order Info');
    }

    public function getSaveUrl(){
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
    
    public function getReportUrl() {
        $dataObject = new Varien_Object(Mage::registry('stockhistory_po_data'));
        return $this->getUrl('stockhistory/adminhtml_transaction/report', array(
                'po_id'    =>    $dataObject->getId(),
        ));    
    }
    
    public function getCreateAmendmentUrl() {
        $dataObject = new Varien_Object(Mage::registry('stockhistory_po_data'));
        return $this->getUrl('stockhistory/adminhtml_transaction/newAmendmentByPo', array(
                //All required fields are given by 'po_id'
//                'vendor_id' => $dataObject->getVendorId(),
//                'vendor_code' => $this->getVendorCode($dataObject->getVendorId()),
                'po_id' => $dataObject->getId(),
//                'category_id' => $dataObject->getCategoryId()
                
        ));
    }
    
    public function getVendorCode($vendorId){
        $vendor = Mage::getModel('stockhistory/vendor')->load($vendorId);
        if(!! $vendor->getId()){
            return $vendor->getVendorCode();
        }
        return null;
    }
    
}