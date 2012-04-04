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
 

class Harapartners_Service_Helper_Rewrite_Catalog_Product extends Mage_Catalog_Helper_Product {
   
    public function initProduct($productId, $controller, $params = null) {
        if (!$params) {
            $params = new Varien_Object();
        }
        if(!$params->getCategoryId()){
        	return false;
        }
        return parent::initProduct($productId, $controller, $params);
    }
    
}