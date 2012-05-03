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

class Harapartners_Service_Model_Rewrite_Catalog_Product extends Mage_Catalog_Model_Product {
	
	//Product out of the live event is NOT salable
	public function isSalable() {
		$eventCategoryFound = false;
		if(!!Mage::registry('current_category')){
			$eventCategoryFound = true;
		}else{
			$helper = Mage::helper('catalog/product');
			if(!!$helper->getLiveCategoryIdFromCategoryEventSort($this)){
				$eventCategoryFound = true;
			}
		}
		
		if($eventCategoryFound){
        	return parent::isSalable();
		}else{
			return false;
		}
    }
	
	public function cleanCache(){
		if(!!Mage::registry('batch_import_no_index')) {
			return $this;
		}else{
			return parent::cleanCache();
		}
	}
   
    public function afterCommitCallback() {
    	// ===== Index rebuild ========================================== //
        //Note URL rewrite is always refreshed
		$urlModel = Mage::getSingleton('catalog/url');
		$urlModel->refreshProductRewrite($this->getId()); //Category path also included
		
		//Create catalog-inventory index only upon product creation!
		if(!$this->getOrigData('entity_id')){
			Mage::getResourceSingleton('cataloginventory/indexer_stock')->reindexProducts(array($this->getId()));
		}
    	
    	if(!!Mage::registry('batch_import_no_index')){
	    	Mage::dispatchEvent('model_save_commit_after', array('object'=>$this));
	        Mage::dispatchEvent($this->_eventPrefix.'_save_commit_after', $this->_getEventData());
	        return $this;
    	}else{
			return parent::afterCommitCallback();
		}
    }
    
    protected function _beforeSave() {
    	//Additional logic here, vender_code, (vender_style) required..
    	parent::_beforeSave();
    	$helper = Mage::helper('ordersplit');
    	if(!in_array($this->getData('fulfillment_type'), $helper->getAllowedFulfillmentTypeArray())){
    		Mage::throwException('Unknown fulfillment type.');
    	}
    	return $this;
    }
    
    //Important logic for legacy order import
	public function getFinalPrice($qty=null){
		if(Mage::registry('order_import_force_product_price')
				&& !!$this->getOrderImportFinalPrice()
		){
			return max(array(0.0, $this->getOrderImportFinalPrice()));
		}
        return parent::getFinalPrice($qty);
    }

}