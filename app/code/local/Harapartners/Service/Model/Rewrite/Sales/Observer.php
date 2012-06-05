<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Service_Model_Rewrite_Sales_Observer extends Mage_Sales_Model_Observer {
   
    public function markQuotesRecollectOnCatalogRules($observer) {
    	//Harapartners, Jun, Performance optimization
//        Mage::getResourceSingleton('sales/quote')->markQuotesRecollectOnCatalogRules();
        return $this;
    }

    public function catalogProductSaveAfter(Varien_Event_Observer $observer) {
    	//Harapartners, Jun, Performance optimization, no action during batch import
    	if(!!Mage::registry('is_batch_import_process')){
    		return $this;
    	}
        $product = $observer->getEvent()->getProduct();
        if ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            return $this;
        }
        Mage::getResourceSingleton('sales/quote')->markQuotesRecollect($product->getId());
        return $this;
    }

}