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

class Harapartners_Unsubcentral_Model_Observer extends Mage_Core_Model_Abstract {
	
	
	
	public function unsubscribeCustomerObserver($observer){
		
		$subscriber = $observer->getEvent()->getSubscriber();
		$customerEmail = $subscriber->getSubscriberEmail();
		$unsubcentralItem = Mage::getModel('unsubcentral/item')->loadByEmail($customerEmail);
		
		
		//If customer already has previous subscription, and in the current save the user unsubscribe
		if(!! $subscriber 
				&& $subscriber->getId()
				&& $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED){ 
			$unsubcentralItem->setData('subscriber_email', $customerEmail);
			$unsubcentralItem->setData('unsubcentral_api_status', Harapartners_Unsubcentral_Model_Item::API_PROCESSING_UNSUBSCRIBE_STATUS);
			$now = Mage::getModel('core/date')->timestamp(time());
			$unsubcentralItem->setData('update_at', date('Y-m-d H:i:s', $now));
			$unsubcentralItem->save();
		}
		
		
		
		if(!! $subscriber 
				&& $subscriber->getId()
				&& $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED
				&& (!$unsubcentralItem || !$unsubcentralItem->getId()) ){ //And if there is no exist $unsubcentralItem record
			$unsubcentralItem->setData('subscriber_email', $customerEmail);
			$unsubcentralItem->setData('unsubcentral_api_status', Harapartners_Unsubcentral_Model_Item::API_PROCESSING_REGISTER_STATUS);
			$now = Mage::getModel('core/date')->timestamp(time());
			$unsubcentralItem->setData('update_at', date('Y-m-d H:i:s', $now));
			$unsubcentralItem->save();
		}
		
		

        return $this;
    }
}