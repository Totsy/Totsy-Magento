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
        foreach ($events as $event) {
            $category = Mage::getModel('catalog/category')
                ->load($event['entity_id']);
            $category->setStoreId(1);

            $this->_processEvent($category);

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

        $events = json_decode($sortentry->getLiveQueue(), true);
        foreach ($events as $event) {
            $category = Mage::getModel('catalog/category')
                ->load($event['entity_id']);
            $category->setStoreId(1);

            $this->_processEvent($category);
        }

        return $this->_getFeedContent();
    }

    /**
     * Process an event for this feed.
     *
     * @param Mage_Catalog_Model_Category $event
     *
     * @return void
     */
    protected abstract function _processEvent(Mage_Catalog_Model_Category $event);

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
