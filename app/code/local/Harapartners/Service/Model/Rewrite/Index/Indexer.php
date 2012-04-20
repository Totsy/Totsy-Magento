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
 
class Harapartners_Service_Model_Rewrite_Index_Indexer extends Mage_Index_Model_Indexer {
    
    public function __construct(){
    	//Attribute and Fulltext ignored, since TopNav uses a complete different search/cache mechanism
    	//Freeing stock status as index, for cart reservation
        $this->_processesCollection = Mage::getResourceModel('index/process_collection')
        		->addFieldToFilter('indexer_code', array("nin"=>array(
        				'catalog_product_attribute', 
        				'catalogsearch_fulltext',
        				'cataloginventory_stock'
        		)
        ));
    }

}