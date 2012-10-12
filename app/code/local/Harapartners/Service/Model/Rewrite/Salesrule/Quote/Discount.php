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

class Harapartners_Service_Model_Rewrite_Salesrule_Quote_Discount extends Mage_SalesRule_Model_Quote_Discount {

    public function collect(Mage_Sales_Model_Quote_Address $address) {
    	
    	//Important logic for importing legacy orders, only apply to the correponding address once!
		if(is_numeric(Mage::registry('order_import_discount_amount'))){
			if(($address->getQuote()->isVirtual() && $address->getAddressType() == 'billing')
					|| (!$address->getQuote()->isVirtual() && $address->getAddressType() == 'shipping')
			){
				$this->_setAddress($address);
				$this->_addAmount(Mage::registry('order_import_discount_amount'));
				$this->_addBaseAmount(Mage::registry('order_import_discount_amount'));
				return $this;
			}
		}
    	
    	$quote = $address->getQuote();
    	$couponCode = $quote->getCouponCode();//may be a pseudo code, or a real code
        $groupcoupon = Mage::getModel('promotionfactory/groupcoupon')->load('pseudo_code', $couponCode);
    	if(!!$groupcoupon && !!$groupcoupon->getId() && $groupcoupon->getUsedCount() == 0){
    		$quote->setCouponCode($groupcoupon->getCode());
    	}
    	parent::collect($address);
    	
    	//if coupon applied successfully and also matching the groupon code, restore the pseudo code and update the description
    	if(!!$quote->getCouponCode()
    			&& !!$groupcoupon && !!$groupcoupon->getId()
    			&& $quote->getCouponCode() == $groupcoupon->getCode()){
    		$this->_updateAddressDescription($groupcoupon, $address);
    		$quote->setCouponCode($couponCode);
    	}
		return $this;
    }
    
	public function _updateAddressDescription($groupcoupon, $address, $separator=', '){
    	$description = $address->getDiscountDescriptionArray();
        if (is_array($description) && !empty($description)) {
            $description = array_unique($description);
        	foreach($description as &$desciptionEntry){
	    		if($desciptionEntry == $groupcoupon->getCode()){
	    			$desciptionEntry = $groupcoupon->getPseudoCode();
	    			break;
	    		}
	    	}
            $description = implode(', ', $description);
        } else {
            $description = '';
        }
        $address->setDiscountDescription($description);
    }
 
}