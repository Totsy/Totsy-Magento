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

class Harapartners_Categoryevent_Block_Adminhtml_Config_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {    
    
    public function __construct(){
        parent::__construct();
        $this->_blockGroup = 'categoryevent';
        $this->_controller = 'adminhtml_config';
        $this->_removeButton('back');
        $this->_updateButton('save', 'label', Mage::helper('categoryevent')->__('Save Config'));
         
    }
    
    public function getHeaderText() {
        return Mage::helper('categoryevent')->__('Configuration');
    }

    public function getSaveUrl(){
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
    
}