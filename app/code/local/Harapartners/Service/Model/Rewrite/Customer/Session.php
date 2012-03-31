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
            $model = Mage::getModel('customertracking/record')->loadByCustomerId($customer->getId());
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