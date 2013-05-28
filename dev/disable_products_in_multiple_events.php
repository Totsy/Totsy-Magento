<?php
/**
 * @category    Totsy
 * @package     Totsy
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

require_once __DIR__ . '/../app/Mage.php';
Mage::app();

$start = time();

/** @var $write Varien_Db_Adapter_Interface $write */
$write = Mage::getSingleton('core/resource')->getConnection('core_write');

$sort = Mage::getModel('categoryevent/sortentry')->loadCurrent()
    ->adjustQueuesForCurrentTime();

$events = array();
$eventIds = array();
if ($sort->hasData('top_live_queue')) {
    $events = array_merge($events, json_decode($sort->getData('top_live_queue'), true));
}
if ($sort->hasData('live_queue')) {
    $events = array_merge($events, json_decode($sort->getData('live_queue'), true));
}

foreach ($events as $event) {
    $eventIds[] = $event['entity_id'];
}

$eventIds = join(',', $eventIds);

$sql = <<<SQL
    SELECT product_id, count(*) FROM core_url_rewrite WHERE store_id = 1 GROUP BY product_id HAVING count(*) > 1;
SQL;

$results = $write->query($sql)->fetchAll(Zend_Db::FETCH_COLUMN, 0);

echo "Found ", count($results), " products associated with more than one live event.", PHP_EOL;
foreach ($results as $productId) {
    if (empty($productId)) {
        continue;
    }

    /** @var $product Mage_Catalog_Model_Product */
    $product = Mage::getModel('catalog/product')->load($productId);

    echo "Analyzing product '", $product->getName(), "' (", $product->getId(), ")", PHP_EOL;

    $categories = $product->getCategoryCollection();
    $categories->addAttributeToSelect('name', 'event_start_date', 'event_end_date')
        ->setOrder('event_end_date', Varien_Data_Collection::SORT_ORDER_DESC);

    if (count($categories) > 1) {
        $categories = $categories->getIterator();
    
        /** @var $firstCategory Mage_Catalog_Model_Category */
        $firstCategory = $categories->current();
    
        echo " * Found ", count($categories), " events associated", PHP_EOL;
        echo " * Decided to keep event '", $firstCategory->getName(), "' (", $firstCategory->getId(), ")", PHP_EOL;
    
        $categories->seek(1);
        while ($category = $categories->current()) {
            echo " * Removing association with event '", $category->getName(), "' (", $category->getId(), ")", PHP_EOL;
            $categoryId = $category->getId();
    
            $sql = "DELETE FROM catalog_category_product_index WHERE `product_id` = $productId AND `category_id` = $categoryId";
            $write->query($sql)->execute();
    
            $sql = "DELETE FROM catalog_category_product WHERE `product_id` = $productId AND `category_id` = $categoryId";
            $write->query($sql)->execute();
    
            $sql = "DELETE FROM core_url_rewrite WHERE `product_id` = $productId AND `category_id` = $categoryId";
            $write->query($sql)->execute();
    
            $categories->next();
        }
    } else {
        echo " * Removing all URL rewrites only", PHP_EOL;
        $sql = "DELETE FROM core_url_rewrite WHERE `product_id` = $productId";
        $write->query($sql)->execute();
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
