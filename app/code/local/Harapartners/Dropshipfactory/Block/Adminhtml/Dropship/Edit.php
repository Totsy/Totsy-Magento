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
class Harapartners_Dropshipfactory_Block_Adminhtml_Dropship_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		$this->_blockGroup = 'dropshipfactory';
		$this->_controller = 'adminhtml_dropship';
		$this->_updateButton('save', 'label', Mage::helper('dropshipfactory')->__('Import'));
	}
	
	public function getHeaderText() {
    	return Mage::helper('dropshipfactory')->__('Drop Ship Tracking Import');
    }

    public function getSaveUrl(){
        return $this->getUrl('*/*/importCSV', array('_current'=>true));
    }
}