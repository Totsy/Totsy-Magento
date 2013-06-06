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

/** @var $write Varien_Db_Adapter_Interface */
$write  = Mage::getSingleton('core/resource')->getConnection('core_write');

$events = array();
if ($argc == 2) {
    $category = Mage::getModel('catalog/category')->load($argv[1]);
    $events[] = $category;
} else {
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
}

$imageCache = array();

foreach ($events as $event) {
    /** @var $products Mage_Catalog_Model_Resource_Product_Collection */
    $products = Mage::getModel('catalog/product')->getCollection()
        ->addAttributeToSelect(array('name', 'image', 'vendor_style'));

    $products->getSelect()->joinInner(
        array('cp' => 'catalog_category_product'),
        'cp.product_id = e.entity_id',
        array()
    )->where("cp.category_id = $event[entity_id]");

    echo "Processing event '", $event['name'], "' (", $event['entity_id'], ")", PHP_EOL;

    /** @var $product Mage_Catalog_Model_Product */
    foreach ($products as $product) {
        if (!$product->getImage()) {
            echo " * Need an image for product '", $product->getName(), "' (", $product->getId(), ") with vendor style '", $product->getData('vendor_style'), "'", PHP_EOL;

            if (isset($imageCache[$product->getData('vendor_style')])) {
                $imagePath = $imageCache[$product->getData('vendor_style')];
                echo " ! Using image already found ($imagePath)", PHP_EOL;
            } else {
                $related = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect(array('name'))
                    ->addAttributeToFilter('image', array('notnull' => true))
                    ->addAttributeToFilter('vendor_style', $product->getData('vendor_style'));

                /** @var $relatedProduct Mage_Catalog_Model_Product */
                foreach ($related as $relatedProduct) {
                    if ($relatedProduct->getData('image') && file_exists(__DIR__ . '/../media/catalog/product' . $relatedProduct->getData('image'))) {
                        echo sprintf(" ! Found related product '%s' (%d) with common vendor style '%s' and image path '%s'%s",
                            $relatedProduct->getName(),
                            $relatedProduct->getId(),
                            $relatedProduct->getData('vendor_style'),
                            $relatedProduct->getData('image'),
                            PHP_EOL
                        );

                        $imagePath = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'product' . $relatedProduct->getData('image');

                        $imageCache[$relatedProduct->getData('vendor_style')] = $imagePath;
                        break;
                    }
                }
            }

            $product->addImageToMediaGallery($imagePath, array('image', 'small_image', 'thumbnail'), false, false)
                ->save();

            $image = $product->getMediaGalleryImages()->getFirstItem();
            if ($image) {
                $productId = $product->getId();
                $imageFile = $image->getData('file');

                $write->query("REPLACE INTO catalog_product_entity_varchar (entity_type_id, attribute_id, store_id, entity_id, value) VALUES (4, 79, 0, $productId, '$imageFile'), (4, 80, 0, $productId, '$imageFile'),(4, 81, 0, $productId, '$imageFile')")->execute();
            }
        }
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
