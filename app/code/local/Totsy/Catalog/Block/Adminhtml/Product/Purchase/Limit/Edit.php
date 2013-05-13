<?php

class Totsy_Catalog_Block_Adminhtml_Product_Purchase_Limit_Edit extends Mage_Adminhtml_Block_Widget_Form_Container{
    
    public function __construct(){
        $this->_blockGroup = 'totsy_catalog';
        $this->_controller = 'adminhtml_product_purchase_limit';
        $this->_headerText = Mage::helper('totsy_catalog')->__('Import product purchase limit');

        parent::__construct();
        $this->_removeButton('back');
        $this->_updateButton('save','label','Import CSV');

    }

 	public function getSaveUrl(){
        return $this->getUrl('*/*/import', array('_current'=>true));
    }
}