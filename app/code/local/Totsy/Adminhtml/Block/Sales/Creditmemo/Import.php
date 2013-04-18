<?php

class Totsy_Adminhtml_Block_Sales_Creditmemo_Import extends Mage_Adminhtml_Block_Widget_Form_Container {    
    
    public function __construct(){
        parent::__construct();
        $this->_removeButton('delete');
        $this->_blockGroup = 'adminhtml';
        $this->_mode = 'import';
        $this->_controller = 'sales_creditmemo';
        $this->_headerText = Mage::helper('sales')->__('Import credits');
    }
   
    public function getSaveUrl(){
        return $this->getUrl('*/*/importcsv', array('_current'=>true));
    }
    
    
}