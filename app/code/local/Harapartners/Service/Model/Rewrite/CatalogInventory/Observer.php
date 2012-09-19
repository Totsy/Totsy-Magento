<?php

/**
 * Harapartners
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Harapartners License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.Harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Harapartners.com so we can send you a copy immediately.
 *
 */

class Harapartners_Service_Model_Rewrite_CatalogInventory_Observer extends Mage_CatalogInventory_Model_Observer {

    //Harapartners, Jun, Special logic for Totsy: Product of the same ID (conf. with different super attr. or simple with differnt options) share the same limit!
    public function checkQuoteItemQty($observer) {
        //Specific Totsy logic, also a quick check (run first)
        $quoteItem = $observer->getEvent()->getItem();
        if (!$quoteItem || !$quoteItem->getProductId() 
                || !$quoteItem->getQuote()
                || $quoteItem->getQuote()->getIsSuperMode()) {
            return $this;
        }
        
        $qty = 0;
        foreach($quoteItem->getQuote()->getAllVisibleItems() as $searchItem){
            if($searchItem->getProductId() == $quoteItem->getProductId()){
                $qty += $searchItem->getQty();
            } 
        }
        
        $maxQuantityByConfig = (float) Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MAX_SALE_QTY);
        if($qty > $maxQuantityByConfig){
            $quoteItem->addErrorInfo(
                    'cataloginventory',
                    Mage_CatalogInventory_Helper_Data::ERROR_QTY_INCREMENTS,
                    Mage::helper('cataloginventory')->__('The maximum quantity allowed for purchase is %s.', $maxQuantityByConfig * 1)
            );
            return $this;
        }
        
        //Due to special logic for cart stock reservation, default stock check is disabled
        //return parent::checkQuoteItemQty($observer);

        return $this;
    }

    
    //Harapartners, Jun, Successful checkout results in stock reservation cancel
    public function reindexQuoteInventory($observer) {
        // Reindex quote ids
        $quote = $observer->getEvent()->getQuote();
        $reservationHelper = Mage::helper('rushcheckout/reservation');
        foreach ($quote->getAllItems() as $quoteItem) {
            $reservationHelper->updateReservationByQuoteItem($quoteItem, true); //Restock all reservation qty, qty also modified by true stock change
        }
        foreach ($this->_itemsForReindex as $item) {
            $item->save();
        }
        return $this;
    }
    
}