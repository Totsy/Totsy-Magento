<?php
class Harapartners_Recurringmembership_Model_Observer {
	
	public function saleOrderPlaceAfter(Varien_Event_Observer $observer) {
		
		$storeId = Mage::app ()->getStore ()->getId ();
		$errorMsgs = array ();
		$order = $observer->getOrder ();
		
		$couponCode =  $order->getQuote()->getCouponCode();
		$groupCoupon = Mage::getModel('promotionfactory/groupcoupon')->loadByPseudoCode($couponCode);
		
		if(!!$groupCoupon && !!$groupCoupon->getId()){
			$groupCoupon->setUsedCount(1);
			$groupCoupon->save();	
		}
		$customer = $order->getQuote()->getCustomer();
		$customerId = $customer->getId();
		$customerEmail = $customer->getEmail();
		
		$emailCoupon = Mage::getModel('promotionfactory/emailcoupon')->loadByEmailCouponWithEmail($couponCode,$customerEmail);		
		if(!!$emailCoupon && !!$emailCoupon->getId()){
			$emailCoupon->setUsedCount(1);
			$emailCoupon->save();	
		}
		
		$orderId = $order->getIncrementId ();
		$items = $order->getItemsCollection ()->getItems ();
		
		//check item if it is membership
		
		//create if not exsiting
		
		// done
		$create = FALSE;
		//get order, loop start
		foreach ( $items as $item ) {
			$sku = $item->getSku ();
			if($sku == 'membershipregister'){
				$create = TRUE;
				$productSku = $sku;
				$productId = $item->getProductId();
			};
		}
		
		$orderRealId = $order->getId();
		
		$cybersourceSubId = $order->getPayment()->getCybersourceSubid();
		$exsitingProfile = Mage::getModel ( 'recurringmembership/profile' )->loadByCustProductId($customerId, $productId);
		
		if($create && (!$exsitingProfile->getId())){
			$newProfile = Mage::getModel ( 'recurringmembership/profile' );
			$newProfile->setData('cust_email',$customerEmail);
			$newProfile->setData('cust_id',$customerId);
			$newProfile->setData('cybersource_subid',$cybersourceSubId);
			$newProfile->setData('product_id',$productId);
			$newProfile->setData('order_id',$orderRealId);
			$newProfile->setData('status',1);
			$newProfile->save();					
		}elseif ($create && ($exsitingProfile->getStatus()== 0)){
			$exsitingProfile->setStatus(1);
			$exsitingProfile->save();
		}
		
		// for coupon count
		
		$couponCode = $order->getQuote()->getCouponCode();
		$customerEmail;
		
		//case 1 for email coupon
		$emailCoupon = Mage::getModel('promotionfactory/emailcoupon')->emailCouponCount($couponCode,$customerEmail);
		if(!!$emailCoupon->getEntityId()){
			$count = $emailCoupon->getUsedCount();
			$emailCoupon->setData('used_count',$count+1);
			$emailCoupon->save();
		}
		//case 2 for group coupon
		$groupCoupon = Mage::getModel('promotionfactory/groupcoupon')->checkPseudoCode($couponCode);
		if(!!$groupCoupon->getEntityId()){
			$count = $groupCoupon->getUsedCount();
			$groupCoupon->setData('used_count',$count+1);
			$groupCoupon->save();
		}		
		return;
	}
}