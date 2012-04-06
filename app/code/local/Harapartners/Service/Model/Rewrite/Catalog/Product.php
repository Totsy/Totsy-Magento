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
   
	public function isSalable() {
		//load event category here!
        return parent::isSalable();
    }
	
	public function cleanCache(){
		if(!!Mage::registry('batch_import_no_index')) {
			return $this;
		}else{
			return parent::cleanCache();
		}
	}
   
    public function afterCommitCallback() {
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
    	return parent::_beforeSave();
    }

}