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
	
	protected function _addAffiliateCustomer($customer){		
		$affiliateCode = Mage::getSingleton('customer/session')->getAffiliateCode();
		if(!!$affiliateCode){		    	
		    $otherParam = Mage::getSingleton('customer/session')->getOtherParam();
		    $subAffiliateCode = Mage::getSingleton('customer/session')->getSubAffiliateCode();
		    $datetime = date('Y-m-d H:i:s');
		    $model = Mage::getModel('customertracking/record')->loadByCustomerEmail($customer->getEmail());
			$affiliate = Mage::getModel('affiliate/record')->loadByAffiliateCode($affiliateCode);
			$registrationParam = array();
		    if(!!$otherParam){		    	
		    	$registrationParam['otherparam'] = $otherParam;	
		    }		    		   
//		    $trackingCode = json_decode($affiliate->getTrackingCode(),true);   
//			foreach ($trackingCode as $label=>$value) {
//				$registrationParam[$label] = $value;
//			}
		    if(!$model->getId()){			   
			    $model->setCreatedAt($datetime);
			    $model->setCustomerId($customer->getId());
			    $model->setAffiliateId($affiliate->getId());
			    $model->setAffiliateCode($affiliateCode);
			    $model->setSubAffiliateCode($subAffiliateCode);
			    if(isset($registrationParam) && !!$registrationParam){
			    	$model->setRegistrationParam(json_encode($registrationParam));
			    }			    
			    $model->setCustomerEmail($customer->getEmail());
			    $model->setLoginCount(0);
			    $model->setPageViewCount(0);
			    $model->save();
			    return true;
		    }
		    return false;
		    // no need to clear customer session
		}		
	    return false;
	}
	
	public function _addAffiliateLoginCount($customer){
		if(!!$customer && !!$customer->getEmail() ){
			$model = Mage::getModel('customertracking/record')->loadByCustomerEmail($customer->getEmail());
			if(!!$model->getId()){
				if(!$model->getLoginCount()){
					$model->setLoginCount(1);
				}else{
					$model->setLoginCount($model->getLoginCount()+1);
				}
				$model->save();
				return true;
			}
			return false;
		}
		return false;
	}
	
//	public function addPageViewCount(Varien_Event_Observer $observer){
//		$customerSession = Mage::getSingleton('customer/session');
//		$customer = $customerSession->getCustomer();
//		if(!!$customer && !!$customer->getEmail() ){
//			$model = Mage::getModel('customertracking/record')->loadByCustomerEmail($customer->getEmail());
//			if(!!$model->getId()&&$observer->getEvent()->getResponse()->getHttpResponseCode()==200){
//				$model->setPageViewCount($model->getPageViewCount()+1);
//				$model->save();
//			}
//		}
//
//		return $this;
//	}
	public function customerRegisterSuccess(Varien_Event_Observer $observer) {
		$customer = $observer->getEvent()->getCustomer();
		$this->_addAffiliateCustomer($customer);
		//plant cookie
		//Harapartners, yang, set register success referal popup cookie here
		$this->_plantRegisterSuccessReferCookie($observer, $customer);
	}
	
	public function loginAfter(Varien_Event_Observer $observer) {
		$customer = $observer->getEvent()->getCustomer();
		Mage::unregister('isLoginPage');
		Mage::register('isLoginPage',true);
		$this->_addAffiliateLoginCount($customer);
		//plant cookie
	}
	
	protected function _plantRegisterSuccessReferCookie($observer, $customer) {
		//set cookie
//		if (!Mage::app()->useCache('full_page')) {
//            return false;
//        }

        $cacheCookie = Mage::getSingleton('enterprise_pagecache/cookie');
        $cacheCookie->setObscure(Harapartners_Customertracking_Helper_Data::COOKIE_CUSTOMER_WELCOME, 'customer_welcome_' . $customer->getId());

//        $cacheId = md5(Enterprise_PageCache_Model_Container_Customer::CACHE_TAG_PREFIX
//            . $cacheCookie->get(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER));
//
//        Enterprise_PageCache_Model_Cache::getCacheInstance()->remove($cacheId);
	        
        return true;
	}
	
}
			