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
			$this->_addAffiliateCustomer($customer);
			//Harapartners, yang, plant cookie, set register success referal popup cookie here
			$this->_plantRegisterSuccessReferCookie($observer, $customer);
		}
	}
	
	public function loginAfter(Varien_Event_Observer $observer) {
		$customer = $observer->getEvent()->getCustomer();
		if(!!$customer && !!$customer->getId()){
			
			TODO: Jun add affiliate id/code/info to the session
			
			$this->_affiliateLoginCountIncrement($customer);
		}
	}
	
	protected function _plantRegisterSuccessReferCookie($observer, $customer) {
//		if (!Mage::app()->useCache('full_page')) {
//            return false;
//        }
        $cacheCookie = Mage::getSingleton('enterprise_pagecache/cookie');
        $cacheCookie->setObscure(Harapartners_Customertracking_Helper_Data::COOKIE_CUSTOMER_WELCOME, 'customer_welcome_' . $customer->getId());
//        $cacheId = md5(Enterprise_PageCache_Model_Container_Customer::CACHE_TAG_PREFIX
//            . $cacheCookie->get(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER));
//        Enterprise_PageCache_Model_Cache::getCacheInstance()->remove($cacheId);
        return true;
	}
	
	protected function _addAffiliateCustomer($customer){		
		$affiliate = Mage::getSingleton('customer/session')->getAffiliate();
		if(!!$affiliate && !!$affiliate->getId()){		    
			$model = Mage::getModel('customertracking/record')->loadByCustomerId($customer->getId());
			
			//check potential conflicts
			if(!!$model && !$model->getId()){
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
				}
				if(!empty($affiliateInfo['registration_param'])){
					$data['registration_param'] = $affiliateInfo['registration_param'];
				}
				try{
					$model->importDataWithValidation($data)->save();
				}catch(Exception $e){
					return false;
				}
			}
		}
	    return true;
	}
	
	public function _affiliateLoginCountIncrement($customer){
		$model = Mage::getModel('customertracking/record')->loadByCustomerEmail($customer->getEmail());
		if(!!$model && !!$model->getId()){
			if(!$model->getLoginCount()){
				$model->setLoginCount(1);
			}else{
				$model->setLoginCount($model->getLoginCount()+1);
			}
			try{
				$model->save();
			}catch(Exception $e){
				return false;
			}
		}
		return true;
	}
	
}
			