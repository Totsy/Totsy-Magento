<?php
/**
 * @category    Totsy
 * @package     Totsy
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

require_once __DIR__ . '/../app/Mage.php';
Mage::app();

$sortentry = Mage::getModel('categoryevent/sortentry')->loadByDate(date('Y-m-d'), 1);
$liveEvents = json_decode($sortentry->getLiveQueue(), true);

$totalProductCount = 0;
foreach ($liveEvents as $event) {
    $category = Mage::getModel('catalog/category')->load($event['entity_id']);
    $products = $category->getProductCollection();

    $simpleProductIds = array();
    $configProducts = array();

    foreach ($products as $product) {
        if ('configurable' == $product->getTypeId()) {
            $configProducts[] = $product;
        } else if ('simple' == $product->getTypeId()) {
            $simpleProductIds[] = $product->getId();
        }
    }

    foreach ($configProducts as $product) {
        $simpletons = $product->getTypeInstance()->getUsedProductCollection()
            ->addAttributeToSelect(array('name', 'size', 'color'));
        foreach ($simpletons as $simpleProduct) {

            if (!in_array($simpleProduct->getId(), $simpleProductIds) && $simpleProduct->getStockItem()->getQty() > 0) {
                echo sprintf(
                    "Product '%s' with attributes Size (%s) and Color (%s) (%d) with %d quantity in stock, part of event '%s'%s",
                    $simpleProduct->getName(),
                    $simpleProduct->getAttributeText('size'),
                    $simpleProduct->getAttributeText('color'),
                    $simpleProduct->getId(),
                    $simpleProduct->getStockItem()->getQty(),
                    $event['name'],
                    PHP_EOL
                );

                if (!in_array('--dry-run', $argv)) {
                    $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($simpleProduct->getId());
                    $stockItem->setData('qty', 0);
                    $stockItem->save();
                }
            }
        }
    }

    $totalProductCount += count($simpleProductIds);
}

echo "Found a total of $totalProductCount live simple products.", PHP_EOL;
