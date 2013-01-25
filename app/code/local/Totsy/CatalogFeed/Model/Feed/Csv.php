<?php
/**
 * @category    Totsy
 * @package     Totsy_CatalogFeed_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_CatalogFeed_Model_Feed_Csv
    extends Totsy_CatalogFeed_Model_Feed_Abstract
{
    /**
     * The CSV column headers.
     *
     * @var array
     */
    protected $_header = array();

    public function generate(array $options = array())
    {
        // initialize the feed data as a temporary resource, with the header line
        $feedcsv = fopen('php://memory', 'rw');

        if ($this->_header) {
            fputcsv($feedcsv, $this->_header);
        }

        /** @var $sortentry Harapartners_Categoryevent_Model_Sortentry */
        $sortentry = Mage::getModel('categoryevent/sortentry')->loadCurrent()
            ->adjustQueuesForCurrentTime();

        $events = json_decode($sortentry->getLiveQueue(), true);
        foreach ($events as $event) {
            $category = Mage::getModel('catalog/category')
                ->load($event['entity_id']);
            $category->setStoreId(1);

            /** @var $layer Mage_Catalog_Model_Layer */
            $layer = Mage::getSingleton('catalog/layer');
            $layer->setCurrentCategory($category);

            $products = $layer->getProductCollection()->addAttributeToSelect(
                array('description', 'departments', 'ages', 'color', 'size')
            );

            $sortby = array_keys(
                Mage::getModel('catalog/config')->getAttributeUsedForSortByArray()
            );
            if (!empty($sortby)) {
                $products->addAttributeToSort($sortby[0]);
            }

            foreach ($products as $product) {
                if ('configurable' == $product->getTypeId()) {
                    /** @var $configurable Mage_Catalog_Model_Product_Type_Configurable */
                    $configurable = $product->getTypeInstance(true);
                    $children = $configurable->getUsedProducts(array('description'), $product);

                    foreach ($children as $child) {
                        fputcsv($feedcsv, $this->_formatFeedItem($category, $child, $product));
                    }
                } else {
                    fputcsv($feedcsv, $this->_formatFeedItem($category, $product));
                }
            }
        }

        rewind($feedcsv);
        return stream_get_contents($feedcsv);
    }

    /**
     * Format a feed item (product) into an array.
     *
     * @param Mage_Catalog_Model_Category $event  The category/event that the
     *  product is part of.
     * @param Mage_Catalog_Model_Product  $child  The product item.
     * @param Mage_Catalog_Model_Product  $parent The parent product item
     *  (configurable) when the product is a child.
     *
     * @return array
     */
    protected function _formatFeedItem(
        Mage_Catalog_Model_Category $event,
        Mage_Catalog_Model_Product $child,
        Mage_Catalog_Model_Product $parent = null)
    {
        return array();
    }
}
