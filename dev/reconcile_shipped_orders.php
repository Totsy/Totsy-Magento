<?php
/**
 * @category    Totsy
 * @package     Totsy
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

$start = time();

require_once __DIR__ . '/../app/Mage.php';
Mage::app();

$dotcom = Mage::helper('fulfillmentfactory/dotcom');

$status = array('processing_fulfillment');

$orders = Mage::getModel('sales/order')->getCollection()
    ->addAttributeToFilter('status', array('in' => $status));

echo "Found ", count($orders), " orders in Processing Fulfillment status.", PHP_EOL;

foreach ($orders as $order) {
    $shipments = $dotcom->getShipmentForOrder($order);

    foreach ($shipments as $shipment) {
        $attr   = $shipment->attributes('i', TRUE);
        $status = (string) $shipment->order_status;
        $order  = Mage::getModel('sales/order')
            ->loadByIncrementId($shipment->client_order_number);

        if (!$order->getId() || $attr['nil'] || 'Shipped' != $status) {
            continue;
        }

        // ensure there is at least one ship item
        $shipmentItems = $shipment->ship_items->children('a', TRUE);
        if (!$shipmentItems) {
            continue;
        }

        // calculate the total quantity shipped, and select the last
        // shipment carrier
        $shipmentQty = 0;
        $shipmentCarrier = "";
        foreach ($shipmentItems as $shipmentItem) {
            $shipmentQty += (int) $shipmentItem->ship_weight;
            $shipmentCarrier = (string) $shipmentItem->carrier;
        }

        $shipmentData = array(
            'total_weight' => (string) $shipment->ship_weight,
            'total_qty'    => $shipmentQty,
            'order_id'     => $order->getId(),
            'carrier_code' => $shipmentCarrier,
        );

        $orderShipments = $order->getShipmentsCollection();
        if(count($orderShipments) > 0) {
            $shipment = $orderShipments->getFirstItem();
        } else {
            $itemQtyArray = array();
            foreach ($order->getAllItems() as $item) {
                $itemQtyArray[$item->getData('item_id')] = (int) $item->getQtyToShip();
            }

            $shipment = Mage::getModel('sales/service_order', $order)
                ->prepareShipment($itemQtyArray);

            // create a new shipment track item
            $shipmentTrack = Mage::getModel('sales/order_shipment_track')
                ->addData($shipmentData);

            // create a new shipment item
            $shipment->addData($shipmentData)
                ->addTrack($shipmentTrack)
                ->save();
        }

        // update the order status and save
        $order->setStatus('complete')->save();

        echo "Order ", $order->getIncrementId(), " marked as shipped.", PHP_EOL;
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
