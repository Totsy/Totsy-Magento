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
	
	private $_csvHeader = array(
			'Product ID', 
			'Product Name', 
			'Product SKU', 
			'Size', 
			'Color', 
			'Vendor SKU', 
			'Qty', 
			'Created At', 
			'Updated At', 
			'Status', 
			'Comment'
	);
	
	public function getCsvHeader(){
		return $this->_csvHeader;
	}
	
	public function getGridVendorTypeArray(){
		return array(
				Harapartners_Stockhistory_Model_Vendor::TYPE_VENDOR => 'Vendor', 
				Harapartners_Stockhistory_Model_Vendor::TYPE_SUBVENDOR =>'Sub Vendor', 
				Harapartners_Stockhistory_Model_Vendor::TYPE_DISTRIBUTOR => 'Distributor'
		);
	}
	
	public function getGridTransactionTypeArray(){
		return array(
				Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_AMENDMENT => 'Amendment', 
				Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_EVENT_IMPORT => 'Event Import', 
				Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_DIRECT_IMPORT => 'Direct Import'
		);
	}
	
	public function getGridTransactionStatusArray(){
		return array(
				Harapartners_Stockhistory_Model_Transaction::STATUS_PENDING => 'Pending', 
				Harapartners_Stockhistory_Model_Transaction::STATUS_PROCESSED => 'Processed', 
				Harapartners_Stockhistory_Model_Transaction::STATUS_FAILED=> 'Failed'
		);

	}
	
	public function getFormVendorTypeArray(){
		return array(
       			array('label' => 'Vendor', 'value' => Harapartners_Stockhistory_Model_Vendor::TYPE_VENDOR),
       			array('label' => 'SubVendor', 'value' => Harapartners_Stockhistory_Model_Vendor::TYPE_SUBVENDOR),
       			array('label' => 'Distributor', 'value' => Harapartners_Stockhistory_Model_Vendor::TYPE_DISTRIBUTOR),
       	);
	}
	
	public function getFormVendorStatusArray(){
		return array(
				array('label' => 'Enabled', 'value' => Harapartners_Stockhistory_Model_Vendor::STATUS_ENABLED),
				array('label' => 'Disabled', 'value' => Harapartners_Stockhistory_Model_Vendor::STATUS_DISABLED),
		);
	}
	
	public function getFormTransactionTypeArray(){
		return array(
       			array('label' => 'Amendment', 'value' => Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_AMENDMENT),
       			array('label' => 'Event Import', 'value' => Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_EVENT_IMPORT),
       			array('label' => 'Direc Import', 'value' => Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_DIRECT_IMPORT),
       	);
	}
	
	public function getFormTransactionStatusArray(){
		return  array(
				array('label' => $this->__('Pending'), 'value' => Harapartners_Stockhistory_Model_Transaction::STATUS_PENDING),
				array('label' => $this->__('Processed'), 'value' => Harapartners_Stockhistory_Model_Transaction::STATUS_PROCESSED ),
				array('label' => $this->__('Failed'), 'value' => Harapartners_Stockhistory_Model_Transaction::STATUS_FAILED),
		);
	}
	
	/**
	 * get products sold by event Id
	 *
	 * @param int $eventId
	 * @return array
	 */
	public function getProductSoldInfoByEvent($eventId) {
		if(empty($eventId)) {
			return array();
		}
		
		$event = Mage::getModel('catalog/category')->load($eventId);
		
		$productsArray = array();
		
		if(!!$event) {
			$productCollection = $event->getProductCollection();
			
			foreach($productCollection as $product) {
				$sku = $product->getSku();
				$productsArray[$sku] = 0;
			}
			
			$orders = Mage::getModel('sales/order')->getCollection()
											->addAttributeToFilter('status', 'pending')
											->addAttributeToFilter('created_at', array(
																					'from' => $event->getData('event_start_date'),
																					'from' => $event->getData('event_end_date'),
																				)
											);
			
			foreach($orders as $order) {
				$items = $order->getAllItems();
				
				foreach($items as $item) {
					$sku = $item->getSku();
					$qty = $item->getQtyOrdered();
					
					if(isset($productsArray[$sku])) {
						$productsArray[$sku] += $qty;
					}
				}
			}											
		}
		
		return $productsArray;
	}
}