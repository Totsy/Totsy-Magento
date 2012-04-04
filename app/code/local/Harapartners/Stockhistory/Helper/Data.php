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

class Harapartners_Stockhistory_Helper_Data extends Mage_Core_Helper_Abstract  {
	
	const STATUS_PENDING = 0;
	const STATUS_PROCESSED = 1;
	const STATUS_FAILED = 2;
	
	const TYPE_VENDOR = 1;
	const TYPE_SUBVENDOR = 2;
	const TYPE_DISTRIBUTOR = 3;
	
	const TRANSACTION_ACTION_AMENDMENT = 1;
	const TRANSACTION_ACTION_EVENT_IMPORT = 2;
	const TRANSACTION_ACTION_DIRECT_IMPORT = 3;
	
	private $csv_header = array('Product ID', 'Product Name', 'Product SKU', 'Size', 'Color', 'Vendor SKU', 'Qty', 'Created At', 'Updated At', 'Status', 'Comment');
//	private $statusOptions = array(
//								'Pending' => 0, 
//								'Processed' => 1, 
//								'Failed'  => 2
//							);

	
	public function getCsvHeader(){
		return $this->csv_header;
	}
	
	public function getFormVendorTypeArray(){
		
		return array(
       			array('label' => 'Vendor', 'value' => self::TYPE_VENDOR),
       			array('label' => 'SubVendor', 'value' => self::TYPE_SUBVENDOR),
       			array('label' => 'Distributor', 'value' => self::TYPE_DISTRIBUTOR),
       	);
	}
	
	public function getFormTransactionTypeArray(){
		
		return array(
       			array('label' => 'Amendment', 'value' => self::TRANSACTION_ACTION_AMENDMENT),
       			array('label' => 'Event Import', 'value' => self::TRANSACTION_ACTION_EVENT_IMPORT),
       			array('label' => 'Direc Import', 'value' => self::TRANSACTION_ACTION_DIRECT_IMPORT),
       	);
	}
	public function getFormVendorStatusArray(){
		return array(
				array('label' => 'Enabled', 'value' => 1),
				array('label' => 'Disabled', 'value' => 0),
		);
	}
	
	public function getStatusOptions(){
		return  array(
				array('value' => 0, 'label' => $this->__('Pending')),
				array('value' => 1, 'label' => $this->__('Processed')),
				array('value' => 2, 'label' => $this->__('Failed')),
		);
		
	}
	
}