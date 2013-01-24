<?php
/**
 * @category    Totsy
 * @package     Totsy
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

$start = time();

require_once __DIR__ . '/../app/Mage.php';
Mage::app();

if ($argc < 2) {
    echo "Usage: php order_repair.php <order increment id>", PHP_EOL;
    exit(1);
}

$order = Mage::getModel('sales/order')->loadByIncrementId($argv[1]);
foreach ($order->getAllItems() as $orderItem) {
    if ($orderItem->getProductType() != 'simple') {
        continue;
    }

    $itemqueues = Mage::getModel('fulfillmentfactory/itemqueue')->getCollection()
        ->addFieldToFilter('order_id', $order->getId())
        ->addFieldToFilter('product_id', $orderItem->getProductId());

    if (!count($itemqueues)) {
        // create a new itemqueue record
        $itemqueue = Mage::getModel('fulfillmentfactory/itemqueue');
        $itemqueue->addData(
            array(
                 'order_item_id'      => $orderItem->getId(),
                 'order_id'           => $order->getId(),
                 'order_increment_id' => $order->getIncrementId(),
                 'store_id'           => $order->getStoreId(),
                 'product_id'         => $orderItem->getProductId(),
                 'sku'                => $orderItem->getSku(),
                 'name'               => $orderItem->getName(),
                 'qty_backordered'    => $orderItem->getQtyBackordered(),
                 'qty_canceled'       => $orderItem->getQtyCanceled(),
                 'qty_invoiced'       => $orderItem->getQtyInvoiced(),
                 'qty_ordered'        => $orderItem->getQtyOrdered(),
                 'qty_refunded'       => $orderItem->getQtyRefunded(),
                 'qty_shipped'        => $orderItem->getQtyShipped(),
                 'fulfill_count'      => 0,
                 'status'             => Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PENDING
            )
        );

        if ($originalQuoteItemId = $orderItem->getQuoteItemId()) {
            $itemqueue->setOriginalQuoteItemId($originalQuoteItemId);
        }

        try {
            $itemqueue->save();
            echo "Created a new ItemQueue record for product ", $orderItem->getSku(), PHP_EOL;
        } catch (Exception $e) {
            echo "Error while creating a new ItemQueue record for product ", $orderItem->getSku(), ":", $e->getMessage(), PHP_EOL;
        }
    }
}

$end = time();

$seconds = $end - $start;
$duration = "$seconds seconds";
if ($seconds > 60) {
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    $duration = "${minutes}m ${seconds}s";
}

echo "The End! Time: $duration, Memory: ", getPeakMemory('MB'), PHP_EOL;

function getPeakMemory($targetUnit = 'kB') {
    $units = array('b', 'kB', 'MB');
    $currentBytes = memory_get_peak_usage();
    $currentUnit  = 'b';

    for ($i = 0; $i < count($units); $i++) {
        if ($units[$i] == $targetUnit) {
            break;
        }

        $currentBytes /= 1024;
        $currentUnit = $units[$i+1];
    }

    return number_format($currentBytes, 1) . ' ' . $currentUnit;
}
