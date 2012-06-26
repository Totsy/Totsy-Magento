<?php
ini_set('memory_limit', '2G');	
$mageFilename = '../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);


$collection = Mage::getModel('sales/order')->getCollection()
					->addAttributeToFilter('status',Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED);

$count = 0;

foreach($collection as $order) {
	try {
		$order->setStatus('pending');
		$order->save();
		$count++;
        #update items queue
        $items = $order->getAllItems();
        foreach($items as $item) {
            $itemQueue = Mage::getModel('fulfillmentfactory/itemqueue')->loadByItemId($item->getId());
            $itemQueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PENDING);
            $itemQueue->save();
        }
	}
	catch(Exception $e) {
		echo 'Order #' . $order->getIncrementId() . ' processed failed' . PHP_EOL;
	}
}

echo $count . ' orders has been changed to Pending status .' . PHP_EOL;

exit;