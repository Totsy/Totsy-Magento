<?php

require_once __DIR__ . '/../app/Mage.php';
Mage::app('admin');

$options = getopt('cl:e:', array('config', 'limit:', 'event:'));

$start = time();

$write  = Mage::getSingleton('core/resource')->getConnection('core_write');

$category = null;
if (isset($options['e'])) {
    $category = Mage::getModel('catalog/category')->load($options['e']);
} else if(isset($options['event'])) {
    $category = Mage::getModel('catalog/category')->load($options['e']);
} else {
    usage();
}

$products = Mage::getModel('catalog/product')->getCollection()
    ->addCategoryFilter($category)
    ->addAttributeToSelect(array('media_gallery', 'image'))
    ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_DISABLED);

$count = 0;
$limit = 100;

if (isset($options['limit'])) {
    $limit = intval($options['limit']);
}
if (isset($options['l'])) {
    $limit = intval($options['l']);
}

$productIds = array();
echo "Analyzing ", count($products), " products", PHP_EOL;
foreach ($products as $product) {
    $stock = Mage::getModel('cataloginventory/stock_item');
    $stock->loadByProduct($product);

    if ($stock->getIsInStock() && $stock->getQty() > 0 && $product->getImage() && file_exists(__DIR__ . '/../media/catalog/product' . $product->getImage()) && filesize(__DIR__ . '/../media/catalog/product' . $product->getImage())) {
        if ('simple' == $product->getTypeId()) {
            $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                ->getParentIdsByChild($product->getId());
            if (count($parentIds)) {
                if (isset($productIds[$parentIds[0]])) {
                    $productIds[$parentIds[0]][] = $product->getId();
                } else {
                    $productIds[$parentIds[0]] = array();
                }
            }
        }
    }
}

foreach ($productIds as $parentId => $children) {
    $categoryId = $category->getId();

    $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 95, 0, $parentId, 4)")->execute();
    $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 89, 0, $parentId, 1)")->execute();
    $write->query("REPLACE INTO catalog_category_product VALUES ($categoryId, $parentId, 1)")->execute();
    $write->query("REPLACE INTO catalog_category_product_index VALUES ($categoryId, $parentId, 1, 1, 1, 4)")->execute();

    foreach ($children as $childId) {
        $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 95, 0, $childId, 1)")->execute();
        $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 89, 0, $childId, 1)")->execute();
        $write->query("REPLACE INTO catalog_category_product VALUES ($categoryId, $childId, 1);")->execute();
        $write->query("REPLACE INTO catalog_category_product_index VALUES ($categoryId, $childId, 1, 1, 1, 1)")->execute();
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

function usage() {
    echo <<<USE
php enable_products.php [options]
Supported options:
  -e, --event=<id>  The category/event identifier whose products will be enabled (required)
  -l, --limit=<num> The limit on the number of products enabled (default: 100)
  -c, --config      Associate parent configurable products when enabling simple products

USE;
    exit(1);
}