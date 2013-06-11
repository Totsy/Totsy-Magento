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

$productMap = array();
$productIds = array();
$imageCopies = array();

echo "Analyzing ", count($products), " products", PHP_EOL;

foreach ($products as $product) {
    $stock = Mage::getModel('cataloginventory/stock_item');
    $stock->loadByProduct($product);

    if ($stock->getIsInStock() && $stock->getQty() > 0 && $product->getImage() && file_exists(__DIR__ . '/../media/catalog/product' . $product->getImage()) && filesize(__DIR__ . '/../media/catalog/product' . $product->getImage())) {
        if ('simple' == $product->getTypeId()) {
            $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                ->getParentIdsByChild($product->getId());
            if (count($parentIds)) {
                $parent = Mage::getModel('catalog/product')->load($parentIds[0]);

                if (!$parent->getImage() || !file_exists(__DIR__ . '/../media/catalog/product' . $parent->getImage()) || !filesize(__DIR__ . '/../media/catalog/product' . $parent->getImage())) {
                    $imageCopies[$parent->getId()] = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . $product->getData('image');
                }

                if (!isset($productMap[$parentIds[0]])) {
                    $productMap[$parentIds[0]] = array();
                }

                $productMap[$parentIds[0]][] = $product->getId();
            } else {
                $productMap[$product->getId()] = array();
            }
        }
    }
}

echo "Found ", count($productMap), " top-level products to enable", PHP_EOL;

foreach ($productMap as $parentId => $children) {
    echo " * Enabling top-level product ", $parentId, " with ", count($children), " children", PHP_EOL;

    $categoryId = $category->getId();

    $write->query("REPLACE INTO catalog_category_product VALUES ($categoryId, $parentId, 1)")->execute();
    $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 95, 0, $parentId, 4)")->execute();
    $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 89, 0, $parentId, 1)")->execute();
    $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 89, 1, $parentId, 1)")->execute();
    $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 89, 3, $parentId, 1)")->execute();
    $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 89, 4, $parentId, 1)")->execute();
    $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 89, 5, $parentId, 1)")->execute();
    $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 89, 6, $parentId, 1)")->execute();
    $write->query("REPLACE INTO catalog_category_product VALUES ($categoryId, $parentId, 1)")->execute();
    $write->query("REPLACE INTO catalog_category_product_index VALUES ($categoryId, $parentId, 1, 1, 1, 4)")->execute();
    $write->query("REPLACE INTO catalog_category_product_index VALUES ($categoryId, $parentId, 1, 1, 3, 4)")->execute();
    $write->query("REPLACE INTO catalog_category_product_index VALUES ($categoryId, $parentId, 1, 1, 4, 4)")->execute();
    $write->query("REPLACE INTO catalog_category_product_index VALUES ($categoryId, $parentId, 1, 1, 5, 4)")->execute();
    $write->query("REPLACE INTO catalog_category_product_index VALUES ($categoryId, $parentId, 1, 1, 6, 4)")->execute();
    $write->query("REPLACE INTO catalog_category_product_index VALUES (2, $parentId, 160001, 0, 1, 4)")->execute();
    $write->query("REPLACE INTO catalog_category_product_index VALUES (2, $parentId, 160001, 0, 3, 4)")->execute();
    $write->query("REPLACE INTO catalog_category_product_index VALUES (2, $parentId, 160001, 0, 4, 4)")->execute();
    $write->query("REPLACE INTO catalog_category_product_index VALUES (2, $parentId, 160001, 0, 5, 4)")->execute();
    $write->query("REPLACE INTO catalog_category_product_index VALUES (2, $parentId, 160001, 0, 6, 4)")->execute();

    foreach ($children as $childId) {
        $write->query("REPLACE INTO catalog_category_product VALUES ($categoryId, $childId, 1)")->execute();
        $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 95, 0, $childId, 1)")->execute();
        $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 89, 0, $childId, 1)")->execute();
        $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 89, 1, $childId, 1)")->execute();
        $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 89, 3, $childId, 1)")->execute();
        $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 89, 4, $childId, 1)")->execute();
        $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 89, 5, $childId, 1)")->execute();
        $write->query("REPLACE INTO catalog_product_entity_int (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 89, 6, $childId, 1)")->execute();
        $write->query("REPLACE INTO catalog_category_product VALUES ($categoryId, $childId, 1);")->execute();
        $write->query("REPLACE INTO catalog_category_product_index VALUES ($categoryId, $childId, 1, 1, 1, 1)")->execute();
        $write->query("REPLACE INTO catalog_category_product_index VALUES ($categoryId, $childId, 1, 1, 3, 1)")->execute();
        $write->query("REPLACE INTO catalog_category_product_index VALUES ($categoryId, $childId, 1, 1, 4, 1)")->execute();
        $write->query("REPLACE INTO catalog_category_product_index VALUES ($categoryId, $childId, 1, 1, 5, 1)")->execute();
        $write->query("REPLACE INTO catalog_category_product_index VALUES ($categoryId, $childId, 1, 1, 6, 1)")->execute();
        $write->query("REPLACE INTO catalog_category_product_index VALUES (2, $childId, 160001, 0, 1, 1)")->execute();
        $write->query("REPLACE INTO catalog_category_product_index VALUES (2, $childId, 160001, 0, 3, 1)")->execute();
        $write->query("REPLACE INTO catalog_category_product_index VALUES (2, $childId, 160001, 0, 4, 1)")->execute();
        $write->query("REPLACE INTO catalog_category_product_index VALUES (2, $childId, 160001, 0, 5, 1)")->execute();
        $write->query("REPLACE INTO catalog_category_product_index VALUES (2, $childId, 160001, 0, 6, 1)")->execute();

        $productIds[] = $childId;
    }

    $productIds[] = $parentId;

    if ($count++ == $limit) {
        break;
    }
}

echo "Found ", count($imageCopies), " products with images that need to be imported", PHP_EOL;
foreach ($imageCopies as $productId => $imagePath) {
    $product = Mage::getModel('catalog/product')->load($productId);

    echo " * Copying image '$imagePath' into product '", $product->getName(), "' (", $product->getId(), ")", PHP_EOL;

    $product->addImageToMediaGallery($imagePath, array('image', 'small_image', 'thumbnail'), false, false)
        ->save();

    $image = $product->getMediaGalleryImages()->getFirstItem();
    if ($image) {
        $imageFile = $image->getData('file');

        $write->query("REPLACE INTO catalog_product_entity_varchar (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 79, 0, $productId, '$imageFile'), (4, 80, 0, $productId, '$imageFile'),(4, 81, 0, $productId, '$imageFile')")->execute();
    }
}

echo "Rebuilding product price index for ", count($productIds), " products", PHP_EOL;
Mage::getResourceSingleton('catalog/product_indexer_price')
    ->reindexProductIds($productIds);

echo "Flushing cache for event (tags: ", join(',', $category->getCacheTags()), ")", PHP_EOL;
Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, $category->getCacheTags());

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
