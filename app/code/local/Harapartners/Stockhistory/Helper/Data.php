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
	
	const STATE_PENDING = 0;
	const STATE_PROCESSED = 1;
	const STATE_FAILED = 2;
	
	private $csv_header = array('Product ID', 'Product Name', 'Product SKU', 'Size', 'Color', 'Vendor SKU', 'Qty', 'Created At', 'Updated At', 'Status', 'Comment');
//	private $statusOptions = array(
//								'Pending' => 0, 
//								'Processed' => 1, 
//								'Failed'  => 2
//							);

	
	public function getCsvHeader(){
		return $this->csv_header;
	}
	
	public function getStatusOptions(){
		$statusOptions = array(
				array('value' => 0, 'label' => $this->__('Pending')),
				array('value' => 1, 'label' => $this->__('Processed')),
				array('value' => 2, 'label' => $this->__('Failed')),
		);
		return $statusOptions;
	}
	
}