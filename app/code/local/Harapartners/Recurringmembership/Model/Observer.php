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

class Harapartners_Recurringmembership_Model_Observer {
	
	//Note only creating membership after payment processed
	public function saleOrderInvoicePay(Varien_Event_Observer $observer) {
		$order = $observer->getEvent()->getOrder();
		if(!$order || !$order->getId()){
			return;
		}
		
		$customer = $order->getCustomer();
		if(!!$customer && !!$customer->getId()){
			return;
		}

		$membershipProductId = null;
		foreach ( $order->getAllItems() as $item ) {
			if($item->getSku() == Harapartners_Recurringmembership_Model_Profile::MEMBERSHIP_PRODUCT_SKU){
				$membershipProductId = $item->getProductId();
				break;
			};
		}
		if(!$membershipProductId){
			return;
		}
		
		$exsitingProfile = Mage::getModel ( 'recurringmembership/profile' )->loadByCustProductId($customer->getId(), $membershipProductId);
		if(!$exsitingProfile || !$exsitingProfile->getId()){
			$newProfile = Mage::getModel ( 'recurringmembership/profile' );
			$newProfile->setData('cust_email', $customerEmail);
			$newProfile->setData('cust_id', $customerId);
			$newProfile->setData('cybersource_subid', $order->getPayment()->getCybersourceSubid());
			$newProfile->setData('product_id', $productId);
			$newProfile->setData('order_id', $order->getId());
			$newProfile->setData('status', 1);
			$newProfile->save();					
		}elseif($exsitingProfile->getStatus() == 0){
			$exsitingProfile->setStatus(1);
			$exsitingProfile->save();
		}
	}
	
}