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
		
		if(!! $subscriber 
				&& $subscriber->getId()
				&& $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED){
			$login = 'api_test';
			$password = 'password1';
			$listId = '142';	
			$url = 'https://login8.unsubcentral.com/uc/address_upload.pl?';
			$fields = array(
					'login' => $login,
					'password' => urlencode($password),
					'listID' => $listId,
					'md5' => 'false',
					'suppressed_text' => urlencode($customerEmail)
			);
			
			foreach($fields as $key => $value) { 
					$fieldsStringArray[] = $key.'='.$value; 
			}
			
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS, implode('&', $fieldsStringArray));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
		}
		
		
		//In customer already has previous subscription, and in the current save the user unsubscribe
		if(!! $subscriber 
				&& $subscriber->getId()
				&& $subscriber->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED
				&& (!$unsubcentralItem || !$unsubcentralItem->getId()) ){ //And if there is no exist $unsubcentralItem record
			$unsubcentralItem = Mage::getModel('unsubcentral/item');
			$unsubcentralItem->setData('subscriber_email', $customerEmail);
			$unsubcentralItem->setData('unsubcentral_api_status', Harapartners_Unsubcentral_Model_Item::API_PENDING_STATUS);
			$now = Mage::getModel('core/date')->timestamp(time());
			$unsubcentralItem->setData('update_at', date('Y-m-d H:i:s', $now));
			$unsubcentralItem->save();
		}
        return $this;
    }
}