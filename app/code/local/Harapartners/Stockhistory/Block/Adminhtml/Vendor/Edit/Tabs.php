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

class Harapartners_Stockhistory_Block_Adminhtml_Vendor_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
 	public function __construct()
    {
        parent::__construct();
        $this->setId('vendor_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('stockhistory')->__('Vendor Information'));
    }
    
    protected function _beforeToHtml()
    {
    	$this->addTab('account', array(
            'label'     => Mage::helper('stockhistory')->__('Account Information'),
            'content'   => $this->getLayout()->createBlock('stockhistory/adminhtml_vendor_edit_form')->toHtml(),
            
        ));
        
        return parent::_beforeToHtml();
    }
    
}