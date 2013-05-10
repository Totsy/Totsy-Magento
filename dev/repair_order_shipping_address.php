<?php

require_once 'app/Mage.php';
Mage::app();

$order = Mage::getModel('sales/order')->loadByIncrementId($argv[1]);
$quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
$shippingAddressId = $order->getData('shipping_address_id');

$orderAddress = Mage::getModel('sales/order_address')->load($shippingAddressId);
$customerAddress = Mage::getModel('customer/address')->load($shippingAddressId);

if ($order->getId() != $orderAddress->getParentId()) {
    echo "Order's shipping address (by order table reference) does not refer back to the order correctly!", PHP_EOL;
    $otherOrder = Mage::getModel('sales/order')->load($orderAddress->getParentId());
    $otherOrder->setData('billing_address_id', $otherOrder->getData('shipping_address_id') - 1);

    echo "Attempting to locate the other order's correct billing address", PHP_EOL;
    $candidates = Mage::getModel('sales/order_address')->getCollection()
        ->addFieldToFilter('parent_id', $otherOrder->getId())
        ->addFieldToFilter('address_type', 'billing');

    echo "Found ", count($candidates), " candidate billing addresses for the other order", PHP_EOL;
    foreach ($candidates as $address) {
        if ($address->getId() == $otherOrder->getData('billing_address_id')) {
            echo "Selected billing address ", $address->getId(), " as the other order's real billing address", PHP_EOL;
            $otherOrder->setData('billing_address_id', $address->getId());
            $otherOrder->save();
        } else if ($address->getId() == $otherOrder->getData('shipping_address_id')) {
            echo "Selected shipping address ", $address->getId(), " as the other order's real shipping address (already set)", PHP_EOL;
        } else {
            echo "Repairing the corrupted billing address and the link back to the original order", PHP_EOL;
            $quoteShippingAddress = $quote->getShippingAddress();
            copyQuoteAddressIntoOrderAddress($quoteShippingAddress, $address);
            $address->setData('parent_id', $order->getId());
            $address->setData('customer_address_id', null);
            $address->setData('address_type', 'shipping');
            $address->save();
        }
    }

    $order->getResource()->updateGridRecords($order->getId());
    $invoices = $order->getInvoiceCollection();
    foreach ($invoices as $invoice) {
        $invoice->getResource()->updateGridRecords($invoice->getId());
        echo $invoice->getIncrementId(), PHP_EOL;
    }

    echo "Repair operation completed on order ", $order->getIncrementId(), " that affected other order ", $otherOrder->getIncrementId(), PHP_EOL;
}

function copyQuoteAddressIntoOrderAddress($quoteAddress, $orderAddress) {
    $copyFields = array('customer_id', 'fax', 'region', 'region_id', 'postcode', 'lastname', 'street', 'city', 'email', 'telephone', 'country_id', 'firstname', 'prefix', 'middlename', 'suffix', 'company');
    foreach ($copyFields as $field) {
        if ($quoteAddress->hasData($field)) {
            $orderAddress->setDataUsingMethod($field, $quoteAddress->getData($field));
        }
    }
}
