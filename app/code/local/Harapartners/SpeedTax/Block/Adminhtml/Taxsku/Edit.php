<?php

class Harapartners_SpeedTax_Block_Adminhtml_Taxsku_Edit extends Mage_Adminhtml_Block_Widget_Form_Container{
    
    public function __construct(){
        $this->_blockGroup = 'speedtax';
        $this->_controller = 'adminhtml_taxsku';
        $this->_headerText = Mage::helper('speedtax')->__('Product Tax Categoties ');
        $this->_addButtonLabel = Mage::helper('speedtax')->__('Generate');

        parent::__construct();
        $this->_removeButton('back');
        $this->_updateButton('save','label','Download CSV');

    }

 	public function getSaveUrl(){
        return $this->getUrl('*/*/generate', array('_current'=>true));
    }
}