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

class Harapartners_Service_Model_Rewrite_Customer_Session extends Mage_Customer_Model_Session {

	const REMEMBER_ME_PERIOD = 2592000; //Harapartners, yang, add remember me time 30 * 24 * 3600, 1 month
	
	protected $_affiliate = null;
	
	//Important step so that cache pages can still display messages
	public function addMessage(Mage_Core_Model_Message_Abstract $message){
		if(Mage::app()->useCache('full_page')){
			$cacheCookie = Mage::getSingleton('enterprise_pagecache/cookie');
			//mt_rand() range to 2^31, which should be sufficient (global messages are transient and deleted after displayed)
	        $cacheCookie->setObscure(Enterprise_PageCache_Model_Cookie::COOKIE_MESSAGE, md5(mt_rand()));
		}
        return parent::addMessage($message);
    }
	
	public function getAffiliate(){
		if(!($this->_affiliate instanceof Harapartners_Affiliate_Model_Record)){
			$this->_affiliate = Mage::getModel('affiliate/record');
			//always try to get the latest affiliate info, setup caching separately if needed
			if(!!$this->getAffiliateId()){
				$this->_affiliate->load($this->getAffiliateId());
			}elseif(!!$this->getAffiliateCode()){
				$this->_affiliate->loadByAffiliateCode($this->getAffiliateCode());
			}else{
				//load from customer tracking record
				$customer = $this->getCustomer();
				if(!!$customer && !!$customer->getId()){
					$customerTrackingRecord = Mage::getModel('customertracking/record')->loadByCustomerEmail($customer->getEmail());
					if(!!$customerTrackingRecord && !!$customerTrackingRecord->getId()){
						$this->_affiliate->load($customerTrackingRecord->getAffiliateId());
					}
				}
			}
		}
		return $this->_affiliate;
	}
	
	public function setAffiliate(Harapartners_Affiliate_Model_Record $affiliate){
		$this->_affiliate = $affiliate;
		//save affiliate ID and code, for future retrieval of the affiliate object
		if(!!$this->_affiliate && !!$this->_affiliate->getId()){
			$this->setAffiliateId($this->_affiliate->getId());
			$this->setAffiliateCode($this->_affiliate->getCode());
		}
		return $this;
	}
    
    public function login($username, $password) {
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

        if ($customer->authenticate($username, $password)) {
            $this->setCustomerAsLoggedIn($customer);
            $this->renewSession();
			//Harapartners, yang, START
			//Add remember me cookie time
            if( Mage::app()->getRequest()->getParam( 'rememberme' ) ) {
	            $this->getCookie()->set('remember_me', 'Remember Me', self::REMEMBER_ME_PERIOD);
            }
			//Harapartners, yang, END
            $model = Mage::getModel('customertracking/record')->loadByCustomerEmail($customer->getEmail());
            if(!!$model->getId()){
            	$this->setAffiliateId($model->getAffiliateId());
            	if($model->getSubAffiliateCode()){
            		$this->setSubAffiliateCode($model->getSubAffiliateCode());
            	}
            if($model->getRegistrationParam()){
            		$this->setRegistrationParam($model->getRegistrationParam());
            	}
            }
            return true;
        }
        return false;
    }

    public function setCustomerAsLoggedIn($customer){
        $this->setCustomer($customer);      
        $this->setData('CUSTOMER_LAST_VALIDATION_TIME', now());		//Harapartners, yang, renew last validation time
        Mage::dispatchEvent('customer_login', array('customer'=>$customer));
        return $this;
    }
}