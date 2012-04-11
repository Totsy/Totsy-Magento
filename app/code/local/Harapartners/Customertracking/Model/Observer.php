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

class Harapartners_Customertracking_Model_Observer {
	
	public function customerRegisterSuccess(Varien_Event_Observer $observer) {
		$customer = $observer->getEvent()->getCustomer();
		if(!!$customer && !!$customer->getId()){
			$this->_addAffiliateCustomerTracking($customer);
			//Harapartners, yang, plant cookie, set register success referal popup cookie here
			$this->_plantRegisterSuccessReferCookie($observer, $customer);
		}
	}
	
	public function loginAfter(Varien_Event_Observer $observer) {
		$customer = $observer->getEvent()->getCustomer();
		if(!!$customer && !!$customer->getId()){
			$this->_loadAffiliateToSession($customer);
		}
	}
	
	public function logoutAfter(Varien_Event_Observer $observer) {
		$cacheCookie = Mage::getSingleton('enterprise_pagecache/cookie');
		$cacheCookie->delete(Harapartners_Customertracking_Helper_Data::COOKIE_CUSTOMER_WELCOME);
        $cacheCookie->delete(Harapartners_Affiliate_Helper_Data::COOKIE_AFFILIATE);
	}
	
	protected function _plantRegisterSuccessReferCookie($observer, $customer) {
		//prepare general welcome is_active cookie (also used for full page cache)
        $cacheCookie = Mage::getSingleton('enterprise_pagecache/cookie');
        $cacheCookie->setObscure(
        		Harapartners_Customertracking_Helper_Data::COOKIE_CUSTOMER_WELCOME,
        		Harapartners_Customertracking_Helper_Data::COOKIE_IS_ACTIVE
        );
		//prepare for full page cache
		if (Mage::app()->useCache('full_page')) {
			$cacheId = md5(Harapartners_Customertracking_Model_Cache_Welcome::CACHE_TAG_PREFIX
	           		. $cacheCookie->get(Harapartners_Customertracking_Helper_Data::COOKIE_CUSTOMER_WELCOME));
	        Enterprise_PageCache_Model_Cache::getCacheInstance()->remove($cacheId);
	    }
        return true;
	}
	
	protected function _addAffiliateCustomerTracking($customer){		
		$affiliate = Mage::getSingleton('customer/session')->getAffiliate();
		if(!!$affiliate && !!$affiliate->getId()){		    
			$customerTrackingRecord = Mage::getModel('customertracking/record')->loadByCustomerEmail($customer->getEmail());
			
			//check potential conflicts
			if(!!$customerTrackingRecord && !$customerTrackingRecord->getId()){
				$data = array(
						'customer_id' => $customer->getId(),
						'customer_email' => $customer->getEmail(),
						'affiliate_id' => $affiliate->getId(),
						'affiliate_code' => $affiliate->getAffiliateCode (),
						'status' => Harapartners_Customertracking_Model_Record::STATUS_NEW,
						'login_count' => 1
				);
				$affiliateInfo = Mage::getSingleton('customer/session')->getData('affiliate_info');
				if(!empty($affiliateInfo['sub_affiliate_code'])){
					$data['sub_affiliate_code'] = $affiliateInfo['sub_affiliate_code'];
					 if(!in_array($data['sub_affiliate_code'] , explode(',', $affiliate->getSubAffiliateCode()))){	   
					 	if(!!$affiliate->getSubAffiliateCode())  {  
	        				$affiliate->setSubAffiliateCode($affiliate->getSubAffiliateCode().','.$data['sub_affiliate_code']);
					 	}else{
					 		$affiliate->setSubAffiliateCode($data['sub_affiliate_code']);
					 	}
	        			$affiliate->save();
	       	 		}
				}
				if(!empty($affiliateInfo['registration_param'])){
					$data['registration_param'] = $affiliateInfo['registration_param'];
				}
				try{
					$customerTrackingRecord->importDataWithValidation($data)->save();
				}catch(Exception $e){
					return false;
				}
			}
		}
	    return true;
	}
	
	protected function _loadAffiliateToSession($customer){
		//no need to reload
		if(!Mage::getSingleton('customer/session')->getAffiliateId()){
			$customerTrackingRecord = Mage::getModel('customertracking/record')->loadByCustomerEmail($customer->getEmail());
			if(!!$customerTrackingRecord && !!$customerTrackingRecord->getId()){
				$affiliate = Mage::getModel('affiliate/record')->load($customerTrackingRecord->getAffiliateId());
				Mage::getSingleton('customer/session')->setAffiliate($affiliate);
				if(!$customerTrackingRecord->getLoginCount()){
					$customerTrackingRecord->setLoginCount(1);
				}else{
					$customerTrackingRecord->setLoginCount($customerTrackingRecord->getLoginCount() + 1);
				}
				try{
					$customerTrackingRecord->save();
				}catch(Exception $e){
					return false;
				}
			}
		}
		
		if(!!Mage::getSingleton('customer/session')->getAffiliateId()
				&& Mage::app()->useCache('full_page')){
			//prepare for full page cache
			$cacheCookie = Mage::getSingleton('enterprise_pagecache/cookie');
	        $cacheCookie->setObscure(
	        		Harapartners_Affiliate_Helper_Data::COOKIE_AFFILIATE,
	        		Mage::getSingleton('customer/session')->getAffiliateId()
	        );
	        $cacheId = md5(Harapartners_Customertracking_Model_Cache_Pixel::CACHE_TAG_PREFIX
	        		. $cacheCookie->get(Harapartners_Affiliate_Helper_Data::COOKIE_AFFILIATE)
	        		. $cacheCookie->get(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER)
	        		. $cacheCookie->get(Harapartners_Customertracking_Helper_Data::COOKIE_CUSTOMER_WELCOME)
	        );	
	        Enterprise_PageCache_Model_Cache::getCacheInstance()->remove($cacheId);
		}

		return true;
	}
	
}
			