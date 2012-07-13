<?php
/**
 * @category    Totsy
 * @package     Totsy_Catalog_Helper
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */
class Totsy_Catalog_Helper_Event
{
    /**
    * This Method look for the Event Name of An Item/Product
    * @param string $itemId
    * @return string
    **/
    public function getName($itemId = null)
    {
        $productId = Mage::getModel('sales/order_item')->load($itemId)->getProductId();
        $product = Mage::getModel('catalog/product')->load($productId);
        $eventName = $product->getCategoryCollection()->getFirstItem()->getName();
        return $eventName;
    }
}