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

class Harapartners_Promotionfactory_Model_Groupcoupon extends Mage_Core_Model_Abstract {
	
    protected function _construct(){
        $this->_init('promotionfactory/groupcoupon');
    }

	//This is for updating 'created_at', 'updated_at' and 'store_id'
    protected function _beforeSave(){
    	//Timezone manipulation ignored. Use Magento default timezone (UTC)
		//$timezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
		$datetime = date('Y-m-d H:i:s');
    	if(!$this->getId()){
    		$this->setData('created_at', $datetime);
    	}
    	$this->setData('updated_at', $datetime);
    	if(!$this->getStoreId()){
    		$this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
    	}
    	parent::_beforeSave();
    }
    
	public function ruleIdExist($ruleId){
    	return $this->getResource()->ruleIdExist($ruleId);
    }
    
    public function getTotalCodeCount($ruleId){
    	return $this->getResource()->getTotalCodeCount($ruleId);
    }
    
	public function deleteByRuleId($ruleId){
    	return $this->getResource()->deleteByRuleId($ruleId);
    }
  	
  	public function loadByPseudoCode($couponCode){
  		$this->addData($this->getResource()->loadByPseudoCode($couponCode));
  		return $this;
  	}
  	
	public function checkPseudoCode($couponCode){
  		$collection = $this->getCollection();
  		$collection->getSelect()->where('code = ?', $couponCode);
  		return $collection->getFirstItem();
  	}
//	public function loadByPaymentProfileKey($paymentProfileKey){
//    	$collection = Mage::getModel('authnetcim/paymentprofile')->getCollection();
//  		$collection->getSelect()->where('md5(`entity_id`) = ?', $paymentProfileKey);//->limit(1);
//    	return $collection->getFirstItem();
//    }
}