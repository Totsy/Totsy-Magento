<?php
/**
 * @category    Totsy
 * @package     Totsy
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

$start = time();

require_once __DIR__ . '/../app/Mage.php';
Mage::app();

$read = Mage::getSingleton('core/resource')->getConnection('core_read');

$select = $read->select()
    ->from(
        array('sfo' => 'sales_flat_order'),
        array('*')
    )
    ->joinLeft(
        array('sfi'=> 'sales_flat_invoice'),
        'sfo.entity_id = sfi.order_id',
        array()
    )
    ->where('sfo.status = "complete"')
    ->where('sfo.status = "processing_fulfillment"')
    ->where('sfo.grand_total > 0')
    ->where('sfo.created_at > "2012-06-1"')
    ->where('sfi.entity_id IS NULL');
    
$stmt = $select->query(Zend_Db::FETCH_ASSOC);
$orders = $stmt->fetchAll();

$countComplete = 0;
foreach ($orders as $orderArray) {
    $order = Mage::getModel('sales/order')->load($orderArray['entity_id']);
    //Reset quantity invoiced for each items
    foreach ($order->getAllItems() as $orderItem) {
        $orderItem->setQtyInvoiced(0.0);
    }
    $invoice = $order->prepareInvoice();

    $invoice->register()
            ->setState(2);

    $invoice->getOrder()->setTotalPaid(
        $invoice->getOrder()->getTotalPaid()+$invoice->getGrandTotal()
    );
    $invoice->getOrder()->setBaseTotalPaid(
        $invoice->getOrder()->getBaseTotalPaid()+$invoice->getBaseGrandTotal()
    );

    $invoice->save();
    $order->save();

    $countComplete++;
}

echo "Completed processing ", count($orders), " invoices: $countComplete created.", PHP_EOL;
