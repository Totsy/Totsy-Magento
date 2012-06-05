<?php
ini_set('memory_limit', '3G');
ini_set('max_input_time', 0);
require_once '../app/Mage.php';
umask(0);
$mageRunCode = isset($_SERVER ['MAGE_RUN_CODE']) ? $_SERVER ['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER ['MAGE_RUN_TYPE']) ? $_SERVER ['MAGE_RUN_TYPE'] : 'store';
//Mage::app();//->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
Mage::app($mageRunCode, $mageRunType);

echo 'Start:' . PHP_EOL;

define('DEFAULT_COLLECTION_SIZE_LIMIT', 100);
$startTime = time();

try {
	//Optimization for large collections, since sales related tables are flat, this is relatively efficient
	//Entity ID offset is safer than LIMIT clause offset, this would safe guard against endless loop
	//LIMIT clause offset is vulnerable if other processes also update the table
	echo 'Pending orders:' . PHP_EOL;
	$entityIdOffset = 0;
	$orderCount = 0;
	$orderItemCount = 0;
	do {
		$orderCollection = Mage::getModel('sales/order')->getCollection();
		$orderCollection->addFieldToFilter('entity_id', array ('gt' => $entityIdOffset))->addFieldToFilter('status', 
				array(
						'pending',
						'processing',
//						'canceled',
						'holded',
//						'complete',
						Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PROCESSING_FULFILLMENT,
						Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_FULFILLMENT_FAILED,
						Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED
		));
		$orderCollection->getSelect()->limit(DEFAULT_COLLECTION_SIZE_LIMIT)->order('entity_id ASC');
		foreach($orderCollection as $order) {
			
			foreach($order->getAllItems() as $orderItem) {
				try {
					validateOrderItem($orderItem, $order);
				
				}
				catch(Exception $e) {
					echo sprintf('Error, (order id) %s, (order item id) %s: %s', $order->getId(), $orderItem->getId(), $e->getMessage()) . PHP_EOL;
				}
				
				$orderItemCount ++;
				
				unset($orderItem);
			}
			
			$entityIdOffset = $order->getId();
			$orderCount ++;
			if($cartCount % 20 == 0) {
				echo sprintf('Processed, %d orders, %d order items, duration %s(s).', $orderCount, $orderItemCount, time() - $startTime) . PHP_EOL;
				$startTime = time();
			}
			unset($order);
		}
		$collectionSize = count($orderCollection);
		unset($orderCollection);
	}
	while($collectionSize >= DEFAULT_COLLECTION_SIZE_LIMIT);
	
	echo 'Finish Rebulid Item Queue' . PHP_EOL;
	
	echo 'Start upadte Item Queue Fulfillment count from DOTcom' . PHP_EOL;
	$inventoryList = Mage::getModel('fulfillmentfactory/service_dotcom')->updateInventory();
	
	Mage::getModel('fulfillmentfactory/service_fulfillment')->stockUpdate($inventoryList);
}
catch(Exception $e) {
	echo $e->getMessage() . PHP_EOL;
}

echo 'End!' . PHP_EOL;

function validateOrderItem($orderItem, $order) {
	
	if($orderItem->getData('product_type') != 'simple') {
		return true; //Only simple item can be dotcom fulfilled
	}
	
	$fulfillmentItem = Mage::getModel('fulfillmentfactory/itemqueue')->loadByItemId($orderItem->getItemId());
	if(! ! $fulfillmentItem->getId()) {
		return true; //Item exists!
	}
	
	//Rebuild the item queue
	
	$fulfillmentItem->setOrderItemId($orderItem->getItemId());
	$fulfillmentItem->setProductId($orderItem->getProductId());
	$fulfillmentItem->setSku($orderItem->getSku());
	$fulfillmentItem->setName($orderItem->getName());
	$fulfillmentItem->setOrderId($order->getId());
	$fulfillmentItem->setOrderIncrementId($order->getIncrementId());
	$fulfillmentItem->setStoreId($order->getStoreId());
	
	$fulfillmentItem->setQtyBackordered($orderItem->getQtyBackordered());
	$fulfillmentItem->setQtyCanceled($orderItem->getQtyCanceled());
	$fulfillmentItem->setQtyInvoiced($orderItem->getQtyInvoiced());
	$fulfillmentItem->setQtyOrdered($orderItem->getQtyOrdered());
	$fulfillmentItem->setQtyRefunded($orderItem->getQtyRefunded());
	$fulfillmentItem->setQtyShipped($orderItem->getQtyShipped());
	
	if(! ! $orderItem->getOriginalQuoteItemId()) {
		$fulfillmentItem->setOriginalQuoteItemId($orderItem->getOriginalQuoteItemId());
	}
	//Original quote item may no longer exists, ignore here
	

	$fulfillmentItem->setFulfillCount(0);
	//$fulfillmentItem->setStatus(getFulfillmentItemStatusByOrderStatus($order->getStatus()));
	

	$fulfillmentItem->save();
	
	updateItemQueueStatusByOrder($fulfillmentItem, $order);
	
	return true;
}

function updateItemQueueStatusByOrder($itemqueue, $order) {
	$status = $order->getStatus();
	
	if($status == 'complete') {
		$itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CLOSED);
	}
	else if($status == 'pending') {
		$itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PENDING);
	}
	else if($status == 'processing') {
		$itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PROCESSING);
		//$itemqueue->setFulfillCount($itemqueue->getQtyOrdered()); //fulfill all items
	}
	else if($status == 'canceled') {
		$itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED);
	}
	else if($status == 'holded') {
		$itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUSPENDED);
	}
	else if($status == Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PROCESSING_FULFILLMENT) {
		$itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUBMITTED);
	}
	else if($status == Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_FULFILLMENT_FAILED) {
		$itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUSPENDED);
	}
	else if($status == Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED) {
		$itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUSPENDED);
	}
	
	$itemqueue->save();
}