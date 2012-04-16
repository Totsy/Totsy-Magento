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
	
}