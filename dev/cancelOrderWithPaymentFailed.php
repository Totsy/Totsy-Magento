<?php
ini_set('memory_limit', '2G');    
$mageFilename = '../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);

$count = 0;
#Limit to X days for Cancel
$limitDate = strtotime("-7 day");

try {
    $orderCollection = Mage::getModel('sales/order')->getCollection()
                    ->addAttributeToFilter('status',Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED);
    foreach($orderCollection as $order) {
        $errorLogCollection = Mage::getModel('fulfillmentfactory/errorlog')->getCollection()
                    ->addFieldToFilter('order_id', $order->getId());
        $cancel = false;
        #Check If There were already ReAuthorization Records
        $lastDateFailed = strtotime("-365 day");
        foreach($errorLogCollection as $erroLog) {
            if(strrchr($erroLog->getMessage(), 'payment')) {
                if(strtotime($erroLog->getUpdatedAt()) > $lastDateFailed) {
                    $lastDateFailed = strtotime($erroLog->getUpdatedAt());
                }
            }
        }
        if($lastDateFailed < $limitDate ) {
            $order->cancel()
                ->save();
            $count++;
        }
    }
} catch(Exception $e) {
    echo 'Order #' . $order->getIncrementId() . ' processed failed ' . $e->getMessages();
}

echo $count . ' orders has been cancelled.' . PHP_EOL;

exit;