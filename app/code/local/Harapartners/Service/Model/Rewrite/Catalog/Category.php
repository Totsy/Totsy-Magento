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

class Harapartners_Service_Model_Rewrite_Catalog_Category extends Mage_Catalog_Model_Category {
	
	//Harapartners, Jun, Event and Top Event are immutable for Totsy logic
	
	public function move($parentId, $afterCategoryId){
		$this->_totsyReserveAnchorCategoryCheck();
    	return parent::move($parentId, $afterCategoryId);
	}
	
	protected function _beforeSave() {
		$this->_totsyReserveAnchorCategoryCheck();
    	return parent::_beforeSave();
    }
    
    protected function _totsyReserveAnchorCategoryCheck(){
    	if($this->getData('name') == Harapartners_Categoryevent_Model_Sortentry::EVENT_CATEGORY_NAME 
    			|| $this->getOrigData('name') == Harapartners_Categoryevent_Model_Sortentry::EVENT_CATEGORY_NAME 
    	){
			throw new Exception('"' . Harapartners_Categoryevent_Model_Sortentry::EVENT_CATEGORY_NAME . '" is a reserved anchor category. You cannot modify the "' . Harapartners_Categoryevent_Model_Sortentry::EVENT_CATEGORY_NAME . '" category or create another category with the same name. Please contact system admin if you need to make low level modifications.');
		}
		
		if($this->getData('name') == Harapartners_Categoryevent_Model_Sortentry::TOP_EVENT_CATEGORY_NAME 
				|| $this->getOrigData('name') == Harapartners_Categoryevent_Model_Sortentry::TOP_EVENT_CATEGORY_NAME
		){
			throw new Exception('"' . Harapartners_Categoryevent_Model_Sortentry::TOP_EVENT_CATEGORY_NAME . '" is a reserved anchor category. You cannot modify the "' . Harapartners_Categoryevent_Model_Sortentry::TOP_EVENT_CATEGORY_NAME . '" category or create another category with the same name. Please contact system admin if you need to make low level modifications.');
		}
		return $this;
    }
    
}
