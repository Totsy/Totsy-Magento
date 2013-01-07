<?php
/**
 * User: troyer
 * Date: 1/7/13
 * Time: 10:22 AM
 */

ini_set('memory_limit', '2G');
$mageFilename = '../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);


$collection = Mage::getModel('sales/order_item')->getCollection()
    ->addAttributeToFilter('sku',array('null' => true))
    ->addAttributeToFilter('product_type','configurable');

$count = 0;

foreach($collection as $orderItem) {
    try {
        $simpleItem = Mage::getModel('sales/order_item')->getCollection()
            ->addAttributeToFilter('parent_item_id',$orderItem->getItemId())
            ->addAttributeToFilter('product_type','simple')
            ->addAttributeToFilter('order_id',$orderItem->getOrderId())
            ->addAttributeToFilter('sku',array('notnull' => true))
            ->getFirstItem();
        if($simpleItem->getSku()) {
            $orderItem->setSku($simpleItem->getSku())->save();
            $count++;
        }
    }
    catch(Exception $e) {
        echo 'Order Item #' . $orderItem->getId() . ' processed failed' . PHP_EOL;
    }
}

echo $count . ' orders skus has been reset .' . PHP_EOL;

exit;