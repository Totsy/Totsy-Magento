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

class Harapartners_Stockhistory_Block_Adminhtml_Transaction_Report_Print extends Harapartners_Stockhistory_Block_Adminhtml_Transaction_Report_Grid {
	
	public function __construct() {
		parent::__construct();
		$this->setTemplate('stockhistory/print.phtml');
	}
	
	public function getVendorInfoObj() {
		$poObj = $this->getPoObject();
		$venderObj = Mage::getModel('stockhistory/vendor')->load($poObj->getData('vendor_id'));
		if (!!$venderObj) {
			return $venderObj;
		}
		return null;
	}
}