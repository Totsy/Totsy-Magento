<?php
ini_set('memory_limit', '3G');
ini_set('max_input_time', 0);
require_once '../app/Mage.php';
umask(0);
$mageRunCode = isset($_SERVER ['MAGE_RUN_CODE']) ? $_SERVER ['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER ['MAGE_RUN_TYPE']) ? $_SERVER ['MAGE_RUN_TYPE'] : 'store';
//Mage::app();//->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
Mage::app($mageRunCode, $mageRunType);

echo 'Start upadte Item Queue Fulfillment count from DOTcom' . PHP_EOL;
$startTime = time();

try {
	$availableProducts = Mage::getModel('fulfillmentfactory/service_dotcom')->updateInventory();
//	$availableProducts[] = array('sku'=>'185-m478fcy98', 'qty'=>100);
	
	
	echo sprintf('# of SKUs from DOTCOM %d: ', count($availableProducts)) . PHP_EOL;
	$skuUpdated = 0;
	
    foreach($availableProducts as $aProduct) {
        $skuUpdated ++;
        
        if(!isset($aProduct['sku']) || !isset($aProduct['qty']) || $aProduct['qty'] <= 0){
            continue;
        }
        
        $availableQty = $aProduct['qty'];
        //get unprocessed ItemQueue collection of this product, based on $aProduct['sku']
        $itemQueueCollection = Mage::getModel('fulfillmentfactory/itemqueue')->getCollection()
                ->loadIncompleteItemQueueByProductSku($aProduct['sku'], $availableQty);
        
        foreach($itemQueueCollection as $itemQueue) {
            //check if we still has available products
            if($availableQty <= 0) {
                break;
            }
            
            //if product is enough, fulfill products for item.
            //if it is not enough, fulfill the rest of the products.
            //consider partial ready items
            $needItemsCount = $itemQueue->getQtyOrdered() - $itemQueue->getFulfillCount();
            
            if($needItemsCount <= $availableQty) {
                $itemQueue->setFulfillCount($itemQueue->getQtyOrdered());
                $itemQueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_READY);
                
                $availableQty -= $needItemsCount;
            }
            else {
                $itemQueue->setFulfillCount($itemQueue->getFulfillCount() + $availableQty);
                $itemQueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PARTIAL);
                
                $availableQty = 0;
            }
            
            //save item queue object
            $itemQueue->save();
            unset($itemQueue);
            
        }
        unset($itemQueueCollection);
        
    	if($skuUpdated % 20 == 0) {
			echo sprintf('Processed, %d SKUs, duration %s(s).', $skuUpdated, time() - $startTime) . PHP_EOL;
			$startTime = time();
		}
        
    }
	
}
catch(Exception $e) {
	echo $e->getMessage() . PHP_EOL;
}

echo 'End!' . PHP_EOL;