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
	
	const ORDER_ITEM_COLLECTION_LOAD_LIMIT = 200;
	
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
	
	public function getFormAllVendorsArray(){
		$allVendorsArray = array(array('label' => '', 'value' => ''));
		$vendorCollection = Mage::getModel('stockhistory/vendor')->getCollection();
		foreach($vendorCollection as $vendor){
			$allVendorsArray[] = array('label' => $vendor->getVendorCode(), 'value' => $vendor->getVendorCode());
		}
		return $allVendorsArray;
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
	
	
	public function getProductSoldInfoByCategory($category, $uniqueProductList){
		$productSoldInfoArray = array();
		$uniqueProductIds = array_keys($uniqueProductList);
		$orderItemCollection = Mage::getModel('sales/order_item')->getCollection()
				->addAttributeToFilter('created_at', array(
						'from' => $category->getData('event_start_date'),
						'to' => $category->getData('event_end_date'),
				)
		);
		
		$orderItemCollection->getSelect()->where('product_id IN(?)', implode(',', $uniqueProductIds));
		
		$currentLoadCount = 0;
		do{
			$tempCollection = clone $orderItemCollection;
			$tempCollection->getSelect()->limit($currentLoadCount, self::ORDER_ITEM_COLLECTION_LOAD_LIMIT);
			foreach($tempCollection as $orderItem){
				$productId = $orderItem->getProductId();
				if(!array_key_exists($productId, $productSoldInfoArray)){
					$productSoldInfoArray[$productId]= array(
							'total' => 0.0,
							'qty'	=> 0.0,
					);
				}
				$tempQty = $orderItem->getQtyOrdered() - $orderItem->getQtyReturned() - $orderItem->getQtyCanceled();
				$uniqueProductList[$productId]['total'] += $orderItem->getPrice() * $tempQty;
				$uniqueProductList[$productId]['qty'] += $tempQty;
			}
			$currentLoadCount += self::ORDER_ITEM_COLLECTION_LOAD_LIMIT;
		}while(count($tempCollection) >= self::ORDER_ITEM_COLLECTION_LOAD_LIMIT);

		
		return $productSoldInfoArray;
	}
	
	/**
	 * get products sold by event Id
	 *
	 * @param int $eventId
	 * @return array
	 */
//	public function getProductSoldInfoByEvent($categoryId) {
//		if(empty($categoryId)) {
//			return array();
//		}
//		
//		
//		//Simple products only
//		
//		$category = Mage::getModel('catalog/category')->load($categoryId);
//		
//		$productsArray = array();
//		
//		if(!!$category) {
//			$productCollection = $category->getProductCollection();
//			
//			foreach($productCollection as $product) {
//				$sku = $product->getSku();
//				$productsArray[$sku] = 0;
//				
//				if($product->getTypeId() == 'configurable') {
//					$childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$product);
//					
//					foreach($childProducts as $cProduct) {
//						$cSku = $cProduct->getSku();
//						$productsArray[$cSku] = 0;
//					}
//				}
//			}
//			
//			$orders = Mage::getModel('sales/order')->getCollection()
//											->addAttributeToFilter('status', array('neq', 'canceled'))
//											->addAttributeToFilter('created_at', array(
//																					'from' => $category->getData('event_start_date'),
//																					'to' => $category->getData('event_end_date'),
//																				)
//											);
//			
//			foreach($orders as $order) {
//				$items = $order->getAllItems();
//				
//				foreach($items as $item) {
//					$sku = $item->getSku();
//					$qty = $item->getQtyOrdered();
//					
//					if(isset($productsArray[$sku])) {
//						$productsArray[$sku] += $qty;
//					}
//				}
//			}											
//		}
//		
//		return $productsArray;
//	}
}