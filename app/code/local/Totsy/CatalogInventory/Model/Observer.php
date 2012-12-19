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

        // Due to special logic for cart stock reservation, default stock check
        // is disabled
        return parent::checkQuoteItemQty($observer);

        //return $this;
    }

    public function reindexQuoteInventory($observer)
    {
        // Reindex quote ids
        $quote = $observer->getEvent()->getQuote();
        $reservationHelper = Mage::helper('rushcheckout/reservation');
        foreach ($quote->getAllItems() as $quoteItem) {
            //Restock all reservation qty, qty also modified by true stock change
            $reservationHelper->updateReservationByQuoteItem($quoteItem, true);
        }

        // any products that required a full save
        foreach ($this->_itemsForReindex as $item) {
            $item->save();

            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            $product->cleanModelCache();
        }

        // avoid calling the parent implementation because we don't want to run
        // the CatalogInventory indexer

        return $this;
    }
}
