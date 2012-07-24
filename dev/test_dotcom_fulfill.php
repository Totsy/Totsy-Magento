<?php

require_once __DIR__ . '/../app/Mage.php';
Mage::app();

$obs = Mage::getModel('fulfillmentfactory/service_dotcom');

try {
    if($argc == 2) {
        $orderArray = array();
        $order = Mage::getModel('sales/order')->load($argv[1]);
        if($order) {
            Mage::helper('fulfillmentfactory')
                ->_pushUniqueOrderIntoArray($orderArray, $order);
            $obs->submitOrdersToFulfill($orderArray, true);
        }
    } else {
        $obs->runDotcomFulfillOrder();
    }
} catch(Exception $e) {
    echo "ERROR: ", $e->getMessage();
}

echo "Complete", PHP_EOL;
