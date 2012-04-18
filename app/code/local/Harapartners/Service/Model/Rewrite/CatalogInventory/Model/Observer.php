<?php

class Harapartners_Service_Model_Rewrite_CatalogInventory_Model_Observer extends Mage_CatalogInventory_Model_Observer {

	//Harapartners, Jun, Special logic for Totsy: Product of the same ID (conf. with different super attr. or simple with differnt options) share the same limit!
	public function checkQuoteItemQty($observer) {
		//Specific Totsy logic, also a quick check (run first)
		$quoteItem = $observer->getEvent()->getItem();
        if (!$quoteItem || !$quoteItem->getProductId() || !$quoteItem->getQuote()
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
        
    	//Parent performs a thorough check against the stock value (relatively slow check)
        return parent::checkQuoteItemQty($observer);
	}
	
	
	
	
	
	//<sales_model_service_quote_submit_before>
	public function subtractQuoteInventory(Varien_Event_Observer $observer) {
        $quote = $observer->getEvent()->getQuote();

        // Maybe we've already processed this quote in some event during order placement
        // e.g. call in event 'sales_model_service_quote_submit_before' and later in 'checkout_submit_all_after'
        if ($quote->getInventoryProcessed()) {
            return;
        }
        $items = $this->_getProductsQty($quote->getAllItems());

        /**
         * Remember items
         */
        $this->_itemsForReindex = Mage::getSingleton('cataloginventory/stock')->registerProductsSale($items);

        $quote->setInventoryProcessed(true);
        return $this;
    }

    //<sales_model_service_quote_submit_failure>
    public function revertQuoteInventory($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $items = $this->_getProductsQty($quote->getAllItems());
        Mage::getSingleton('cataloginventory/stock')->revertProductsSale($items);

        // Clear flag, so if order placement retried again with success - it will be processed
        $quote->setInventoryProcessed(false);
    }
    
    //<sales_model_service_quote_submit_success>
	public function reindexQuoteInventory($observer) {
        // Reindex quote ids
        $quote = $observer->getEvent()->getQuote();
        $productIds = array();
        foreach ($quote->getAllItems() as $item) {
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
        foreach ($this->_itemsForReindex as $item) {
            $item->save();
            $productIds[] = $item->getProductId();
        }
        Mage::getResourceSingleton('catalog/product_indexer_price')->reindexProductIds($productIds);

        $this->_itemsForReindex = array(); // Clear list of remembered items - we don't need it anymore

        return $this;
    }
	
}