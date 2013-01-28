<?php
/**
 * @category    Totsy
 * @package     Totsy_CatalogFeed_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_CatalogFeed_Model_Feed_Json_Sailthru
    extends Totsy_CatalogFeed_Model_Feed_Json
{
    /**
     * Process an event for this feed.
     *
     * @param Mage_Catalog_Model_Category $event
     *
     * @return void
     */
    protected function _processEvent(Mage_Catalog_Model_Category $event)
    {
    }

    /**
     * Process a product for this feed.
     *
     * @param Mage_Catalog_Model_Category $event
     * @param Mage_Catalog_Model_Product  $product
     * @param Mage_Catalog_Model_Product  $parent
     *
     * @return void
     */
    protected function _processProduct(
        Mage_Catalog_Model_Category $event,
        Mage_Catalog_Model_Product $product,
        Mage_Catalog_Model_Product $parent = null
    ) {
    }

}
