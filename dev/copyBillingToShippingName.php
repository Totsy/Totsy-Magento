<?php
ini_set('memory_limit', '2G');
$mageFilename = '../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);

$count = 0;
$updated = 0;

try {
    $orderAddressCollection = Mage::getModel('sales/order_address')->getCollection()
        ->addAttributeToFilter('lastname', array('null' => true))
        ->addAttributeToFilter('address_type','shipping');
    foreach($orderAddressCollection as $shippingAddress) {
        $billingAddress = Mage::getModel('sales/order_address')->getCollection()
            ->addAttributeToFilter('parent_id',$shippingAddress->getParentId())
            ->addAttributeToFilter('address_type','billing')
            ->getFirstItem();
        if (strpos($shippingAddress->getFirstname(),$billingAddress->getFirstname()) !== false
            && strpos($shippingAddress->getFirstname(),$billingAddress->getLastname()) !== false
        ) {
            $shippingAddress->setData('lastname',$billingAddress->getLastname())
                ->setData('firstname',$billingAddress->getFirstname())
                ->save();
            $updated++;
        }
        $count++;
    }
} catch(Exception $e) {
    echo 'Order Address #' . $shippingAddress->getId() . ' processed failed ' . $e->getMessage();
}
echo $count . 'Shipping address with empty last name ';
echo $updated . 'Shipping address have been updated with billing address name';
exit;

