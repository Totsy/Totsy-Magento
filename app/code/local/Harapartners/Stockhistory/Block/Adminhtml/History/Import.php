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

class Harapartners_Stockhistory_Block_Adminhtml_History_Import extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		$this->_blockGroup = 'stockhistory';
		$this->_controller = 'adminhtml_history';
		$this->_mode = 'import';
		$this->_removeButton('save');
		$this->_addButton('importsave', array(
            'label'     => Mage::helper('stockhistory')->__('Save File'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/saveImport') .'\')',
			//'class'		=> 'save',
      	));
	}
	
	public function getHeaderText() {
    	return Mage::helper('stockhistory')->__('Purchase Order Import');
    }

    public function getSaveUrl(){
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
    
	
}