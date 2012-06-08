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

class Harapartners_Service_Model_Rewrite_Sales_Quote extends Mage_Sales_Model_Quote {
   
    public function addProductAdvanced(Mage_Catalog_Model_Product $product, $request = null, $processMode = null) {
    	//Harapartners, Jun, check if product is salable due to category/event limit
    	if(!$product->isSalable()){
    		Mage::throwException(sprintf('The selected item \'%s\' is not available.', $product->getName()));
    	}
    	return parent::addProductAdvanced($product, $request, $processMode);
    }

}