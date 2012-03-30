<?php
class Harapartners_Rushcheckout_Block_Adminhtml_Timerconfig_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {	
	
	public function __construct(){
    	parent::__construct();
    	
        $this->_blockGroup = 'rushcheckout';
        $this->_controller = 'adminhtml_timerconfig';
        $this->_removeButton('back');
        $this->_updateButton('save', 'label', Mage::helper('rushcheckout')->__('Save Config'));
         
    }
    
    public function getHeaderText() {
    	return Mage::helper('rushcheckout')->__('Cart Timer Configuration');
    }

    public function getSaveUrl(){
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
    
}