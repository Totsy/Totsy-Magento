<?php
/**
 * @category    Totsy
 * @package     Totsy_CatalogFeed_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

abstract class Totsy_CatalogFeed_Model_Feed_Abstract
{
    /**
     * Feed options that affect the feed content in some way.
     *
     * @var array
     */
    protected $_options;

    public function __construct(array $options = array())
    {
        $this->_options = $options;

        // use the default 'totsy' store so that URLs are generated for the frontend
        Mage::app()->setCurrentStore(1);
    }

    /**
     * Generate a feed, and return it's string contents.
     *
     * @return string
     */
    public function generate()
    {
        /** @var $sortentry Harapartners_Categoryevent_Model_Sortentry */
        $sortentry = Mage::getModel('categoryevent/sortentry')->loadCurrent()
            ->adjustQueuesForCurrentTime();

        $events = json_decode($sortentry->getLiveQueue(), true);
        if ($toplive = json_decode($sortentry->getTopLiveQueue(), true)) {
            $events = array_merge($toplive, $events);
        }

        foreach ($events as $event) {
            $category = null;
            if (!isset($this->_options['supress_event_load'])) {
                $category = Mage::getModel('catalog/category')
                    ->load($event['entity_id']);
                $category->setStoreId(1);
            }

            $this->_processEvent($category, $event);

            if (isset($this->_options['supress_product_load'])) {
                continue;
            }

            /** @var $layer Mage_Catalog_Model_Layer */
            $layer = Mage::getSingleton('catalog/layer');
            $layer->setCurrentCategory($category);

            $products = $layer->getProductCollection();

            $sortby = array_keys(
                Mage::getModel('catalog/config')->getAttributeUsedForSortByArray()
            );
            if (!empty($sortby)) {
                $products->addAttributeToSort($sortby[0]);
            }

            foreach ($products as $product) {
                $product->setCategoryId($event['entity_id']);
                if ('configurable' == $product->getTypeId()) {
                    $configurable = $product->getTypeInstance(true);
                    $children = $configurable
                        ->getUsedProducts(array('description'), $product);

                    foreach ($children as $child) {
                        $this->_processProduct($category, $child, $product);
                    }
                } else {
                    $this->_processProduct($category, $product);
                }
            }
        }

        return $this->_getFeedContent();
    }

    /**
     * Process an event for this feed.
     *
     * @param Mage_Catalog_Model_Category $event
     * @param array                       $categoryInfo
     *
     * @return void
     */
    protected abstract function _processEvent(
        Mage_Catalog_Model_Category $event = null,
        array $categoryInfo = array()
    );

    /**
     * Process a product for this feed.
     *
     * @param Mage_Catalog_Model_Category $event
     * @param Mage_Catalog_Model_Product  $product
     * @param Mage_Catalog_Model_Product  $parent
     *
     * @return void
     */
    protected abstract function _processProduct(
        Mage_Catalog_Model_Category $event,
        Mage_Catalog_Model_Product  $product,
        Mage_Catalog_Model_Product  $parent = null
    );

    /**
     * Build the string contents of this feed. This is called after the feed
     * has been built inside the generate() method.
     *
     * @return string
     */
    protected abstract function _getFeedContent();
}
