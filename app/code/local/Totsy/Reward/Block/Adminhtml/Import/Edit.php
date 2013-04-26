<?php

class Totsy_Reward_Block_Adminhtml_Import_Edit extends Mage_Adminhtml_Block_Widget_Form_Container{
    
    public function __construct(){
        $this->_blockGroup = 'totsy_reward';
        $this->_controller = 'adminhtml_import';
        $this->_headerText = Mage::helper('enterprise_reward')->__('Import credits');
        $this->_addButtonLabel = Mage::helper('enterprise_reward')->__('Generate');

        parent::__construct();
        $this->_removeButton('back');
        $this->_updateButton('save','label','Import CSV');

    }

 	public function getSaveUrl(){
        return $this->getUrl('*/*/import', array('_current'=>true));
    }
}