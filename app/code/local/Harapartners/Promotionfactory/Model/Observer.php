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

class Harapartners_Promotionfactory_Model_Observer {
	
	public function saleOrderPlaceAfter(Varien_Event_Observer $observer) {
		$order = $observer->getEvent()->getOrder();
		if(!$order || !$order->getId()){
			return;
		}
		
		$couponCode = $order->getQuote()->getCouponCode();
		if(!$couponCode){
			return;
		}
		
		$groupCoupon = Mage::getModel('promotionfactory/groupcoupon')->loadByPseudoCode($couponCode);
		if(!!$groupCoupon && !!$groupCoupon->getId()){
			$groupCoupon->setData('used_count', $groupCoupon->getUsedCount() + 1);
			$groupCoupon->save();	
		}
		
		
		$customer = $order->getCustomer();
		if(!!$customer && !!$customer->getId()){
			$emailCoupon = Mage::getModel('promotionfactory/emailcoupon')->loadByEmailCouponWithEmail($couponCode, $customer->getEmail());		
			if(!!$emailCoupon && !!$emailCoupon->getId()){
				$emailCoupon->setData('used_count', $emailCoupon->getUsedCount() + 1);
				$emailCoupon->save();	
			}
		}
	}
	
}