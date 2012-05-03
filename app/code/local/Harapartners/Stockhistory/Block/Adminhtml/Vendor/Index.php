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

class Harapartners_Stockhistory_Block_Adminhtml_Vendor_Index extends Mage_Adminhtml_Block_Widget_Grid_Container{
    
    public function __construct(){
        $this->_blockGroup = 'stockhistory';
        $this->_controller = 'adminhtml_vendor_index';
        $this->_headerText = Mage::helper('stockhistory')->__('Vendor Info');
        parent::__construct();
    }
 
}