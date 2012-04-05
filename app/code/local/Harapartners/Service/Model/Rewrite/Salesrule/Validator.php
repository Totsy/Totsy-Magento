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

class Harapartners_Service_Model_Rewrite_Salesrule_Validator extends Mage_SalesRule_Model_Validator {
	
	const AUTO_COUPON_AFTER_FIRST_ORDER_TIME = 2592000; // 30day
	const AUTO_TEN_RULE_ID = 3 ;
	
	public function canApplyAutoTen($address) {
		$firstOrder = $address->getCustomerOrderCollection()->getFirstItem();
		
		$firstOrderCreatedAt= $firstOrder->getData('created_at');
		if (count($address->getCustomerOrderCollection()) == 1 && strtotime($firstOrderCreatedAt) + self::AUTO_COUPON_AFTER_FIRST_ORDER_TIME > strtotime(now())){
			return TRUE;
		}
    		return FALSE;
	}
	
	protected function _canProcessRule($rule, $address){
		
		//check condition 1
		$ruleId = $rule->getId();
		if($ruleId == self::AUTO_TEN_RULE_ID && !$this->canApplyAutoTen($address)){
			return FALSE;
		}
		
		$ruleExsit = Mage::getModel('promotionfactory/emailcoupon')->ruleIdExist($ruleId);
		$custEmail = $address->getQuote()->getCustomer()->getEmail() ;
		$emailCouponMatchFail = False;
		$emailCouponMacthFail = Mage::getModel('promotionfactory/emailcoupon')->emailCouponMacthFail($ruleId,$custEmail);
		
		if($ruleExsit && $emailCouponMacthFail){
			return false;
		}
		
        return parent::_canProcessRule($rule, $address);
    }
    
	public function process(Mage_Sales_Model_Quote_Item_Abstract $item){
		if(!Mage::registry('isSplitOrder')){			
			return parent::process($item);
        }
        return $this;
	}
    
}