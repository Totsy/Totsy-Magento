<?php
/**
 * @category    Totsy
 * @package     Totsy
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

require_once __DIR__ . '/../app/Mage.php';
Mage::app('admin');

if ($argc < 3) {
    usage();
}

$start = time();

$write  = Mage::getSingleton('core/resource')->getConnection('core_write');

$categoryId = intval($argv[1]);
$direction = '+';
$delta = $argv[2];
$deltaType = '$';

if ($delta{0} == '-') {
    $direction = '-';
    $delta = substr($delta, 1);
} else if ($delta{0} == '+') {
    $direction = '+';
    $delta = substr($delta, 1);
}

if ($delta{0} == '$') {
    $deltaType = '$';
    $delta = floatval(substr($delta, 1));
} else if (substr($delta, -1) == '%') {
    $deltaType = '%';
    $delta = floatval(substr($delta, 0, strlen($delta)-1));
}

if ($direction == '-') {
    $delta *= -1;
}

$category = Mage::getModel('catalog/category')->load($categoryId);

/** @var $products Mage_Catalog_Model_Resource_Product_Collection */
$products = Mage::getModel('catalog/product')->getCollection()
    ->addAttributeToSelect(array('special_price', 'name'));

$products->getSelect()->joinInner(array('cp' => 'catalog_category_product'), 'cp.product_id = e.entity_id', array())
    ->where("cp.category_id = $categoryId");

echo "Updating prices for ", count($products), " products", PHP_EOL;

/** @var $product Mage_Catalog_Model_Product */
foreach ($products as $product) {
    $currentPrice = $newPrice = $product->getData('special_price');

    if ($deltaType == '$') {
        $newPrice = $currentPrice + $delta;
    } else if ($deltaType == '%') {
        $newPrice = $currentPrice + ($currentPrice * $delta / 100);
    }

    $newPrice = ceil($newPrice);

    echo sprintf(" * Changing price for product '%s' (%d) from $%f to $%f%s",
        $product->getData('name'),
        $product->getId(),
        $currentPrice,
        $newPrice,
        PHP_EOL
    );

    $product->setData('special_price', $newPrice);
    $product->getResource()->saveAttribute($product, 'special_price');
}

echo "Re-indexing product prices", PHP_EOL;

Mage::getResourceSingleton('catalog/product_indexer_price')
    ->reindexProductIds($products->getAllIds());

$end = time();

$seconds = $end - $start;
$duration = "$seconds seconds";
if ($seconds > 60) {
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    $duration = "${minutes}m ${seconds}s";
}

echo "Time: $duration, Memory: ", getPeakMemory('MB'), PHP_EOL;

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

function usage() {
    echo <<<USE
php reprice_event.php <id> <delta>
  <id>    is the event identifier (numeric) and
  <delta> is the price difference to apply to each product in the event,
          expressed as a dollar amount ($1.00) or a percentage (10%)

USE;
    exit(1);
}