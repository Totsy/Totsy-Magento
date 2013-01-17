<?php
/**
 * @category    Totsy
 * @package     Totsy_CatalogInventory_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_CatalogInventory_Model_Observer
    extends Mage_CatalogInventory_Model_Observer
{
    /**
     * Harapartners, Jun
     * Special logic for Totsy: Product of the same ID (conf. with different
     * super attr. or simple with differnt options) share the same limit!
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Totsy_CatalogInventory_Model_Observer
     */
    public function checkQuoteItemQty($observer)
    {
        // Specific Totsy logic, also a quick check (run first)
        $quoteItem = $observer->getEvent()->getItem();
        if (!$quoteItem || !$quoteItem->getProductId() || !$quoteItem->getQuote() ||
            $quoteItem->getQuote()->getIsSuperMode()
        ) {
            return $this;
        }

        $qty = 0;
        foreach ($quoteItem->getQuote()->getAllVisibleItems() as $searchItem) {
            if ($searchItem->getProductId() == $quoteItem->getProductId()) {
                $qty += $searchItem->getQty();
            }
        }

        $maxQuantityByConfig = (float) Mage::getStoreConfig(
            Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MAX_SALE_QTY
        );

        if ($qty > $maxQuantityByConfig) {
            $quoteItem->addErrorInfo(
                'cataloginventory',
                Mage_CatalogInventory_Helper_Data::ERROR_QTY_INCREMENTS,
                Mage::helper('cataloginventory')->__(
                    'The maximum quantity allowed for purchase is %s.',
                    $maxQuantityByConfig * 1
                )
            );
        }

        return parent::checkQuoteItemQty($observer);

        //return $this;
    }

    public function reindexQuoteInventory($observer)
    {
        // Reindex quote ids
        $quote = $observer->getEvent()->getQuote();
        $reservationHelper = Mage::helper('rushcheckout/reservation');
        $productIds = array();
        foreach ($quote->getAllItems() as $item) {
            //Restock all reservation qty, qty also modified by true stock change
            $reservationHelper->updateReservationByQuoteItem($item, true);

            $productIds[$item->getProductId()] = $item->getProductId();
            $children   = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $productIds[$childItem->getProductId()] = $childItem->getProductId();
                }
            }
        }

        if( count($productIds)) {
            Mage::getResourceSingleton('cataloginventory/indexer_stock')->reindexProducts($productIds);
        }

        // Reindex previously remembered items
        $productIds = array();
        // any products that required a full save
        foreach ($this->_itemsForReindex as $item) {
            $item->save();

            $productIds[] = $item->getProductId();
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $product->cleanModelCache();
        }

        Mage::getResourceSingleton('catalog/product_indexer_price')->reindexProductIds($productIds);

        $this->_itemsForReindex = array(); // Clear list of remembered items - we don't need it anymore

        return $this;
    }

    public function catalogInventoryStockItemSaveAfter($observer) {

        /**
         * Mage_CatalogInventory_Model_Stock_Item
         * @var Mage_CatalogInventory_Model_Stock_Item
         */
        $item = $observer->getItem();

        if($item->getStockStatusChangedAuto() || ($item->getOriginalInventoryQty() <= 0 && $item->getQty() > 0 && $item->getQtyCorrection() > 0)) //If the stock status changed
        {
            $parentIdsConfigurable = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($item->getProductId());
            $parentIdsGrouped = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($item->getProductId());
            $parentIds = array_merge($parentIdsConfigurable,$parentIdsGrouped);

            if($parentIds)
            {
                foreach ($parentIds as $id)
                {
                    $product = Mage::getModel('catalog/product')->load($id);
                    $product->cleanModelCache();
                }
            }

            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $product->cleanModelCache();
        }
    }
}
