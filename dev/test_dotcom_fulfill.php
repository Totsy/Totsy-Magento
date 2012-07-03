<?php
require_once '../app/Mage.php';
Mage::app();

try{
    $longopts  = array("order_id::");
    $parameters = getopt(null,$longopts);
    
    if($parameters['order_id']) {
        $orderArray = array();
        $order = Mage::getModel('sales/order')->load($parameters['order_id']);
        if($order) {
            Mage::helper('fulfillmentfactory')->_pushUniqueOrderIntoArray($orderArray, $order);
            $obs = Mage::getModel('fulfillmentfactory/service_dotcom');
            $obs->submitOrdersToFulfill($orderArray, true);
        }
    }
} catch(Exception $e) {
    echo $e->getMessage();
}
echo 'End!';