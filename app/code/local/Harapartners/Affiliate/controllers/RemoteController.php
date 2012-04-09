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

class Harapartners_Affiliate_RemoteController extends Mage_Core_Controller_Front_Action{

    public function registerAction(){
    	$response = Mage::app()->getResponse();
		$response->setHeader('Content-type', 'application/json');
    	$request = $this->getRequest();
    	$email = $request->getParam('email');
    	$key = $request->getParam('key');
        $affiliateCode = $this->formatCode($request->getParam('affiliate_code'));
        $affiliate = Mage::getModel('affiliate/record')->loadByAffiliateCode($affiliateCode);    
        $session = Mage::getSingleton('customer/session');   
        if(!!$affiliate && !!$affiliate->getId()){
        	$affiliateInfo = array();
	        $subAffiliateCode = $request->getParam('sub_affiliate_code');
	        if(!!$subAffiliateCode){
	        	$affiliateInfo['sub_affiliate_code'] = $subAffiliateCode;
	        }
	        
	        $affiliateInfo['registration_param'] = json_encode($request->getParams());
	        $session->setData('affiliate_id', $affiliate->getId());
	        $session->setData('affiliate_info', $affiliateInfo);
	       
        }
        $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);
		if(!$key){
			$result = 'invalid key.';
		}elseif(!$email && !Zend_Validate::is($email, 'EmailAddress')) {
            $result = 'invalid email address.';
        }elseif(!!$customer->getId()){
            $result = 'customer with the email already existed.';
        }else{
        	//create account with random password associated with affiliate 
        	//send email notification
        	try{
	        	$password = $customer->generatePassword();
	        	$customer->setEmail($email);
	        	$customer->setpassword($password);
	           	Mage::register('new_account',1);//Harapartners, Edward, Start of setting for Affliate email validation
	            $customer->save();
				Mage::unregister('new_account');//Harapartners, Edward, End of setting for Affliate email validation 
				Mage::dispatchEvent('customer_register_success',array('account_controller' => $this, 'customer' => $customer));// for affiliate customer tracking
        	}catch(Exception $e){
        		$result='account creation failed'.$e->getMessage();
        	}
        	$customer->sendPasswordReminderEmail();//can be specifed email later.
        	$result = 'account created you will reveiv e a email including your temporal password';
        }
        $response->setBody($result);
    }
    public function loginAction(){
    	$response = Mage::app()->getResponse();
		$response->setHeader('Content-type', 'application/json');
    	$request = $this->getRequest();
    	$email = $request->getParam('email');
    	$key = $request->getParam('key');
        $affiliateCode = $this->formatCode($request->getParam('affiliate_code'));
        $affiliate = Mage::getModel('affiliate/record')->loadByAffiliateCode($affiliateCode);    
        $session = Mage::getSingleton('customer/session');   
        if(!!$affiliate && !!$affiliate->getId()){
        	$affiliateInfo = array();
	        $subAffiliateCode = $request->getParam('sub_affiliate_code');
	        if(!!$subAffiliateCode){
	        	$affiliateInfo['sub_affiliate_code'] = $subAffiliateCode;
	        }
	        
	        $affiliateInfo['registration_param'] = json_encode($request->getParams());
	        $session->setData('affiliate_id', $affiliate->getId());
	        $session->setData('affiliate_info', $affiliateInfo);
	       
        }
        $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);
		if(!$key){
			$result = 'invalid key.';
		}elseif(!$email && !Zend_Validate::is($email, 'EmailAddress')) {
            $result = 'invalid email address.';
        }elseif(!!$customer->getId()){
            $result = 'customer with the email already existed.';
        }else{
        	//create account with random password associated with affiliate 
        	//send email notification
        	try{
	        	$password = $customer->generatePassword();
	        	$customer->setEmail($email);
	        	$customer->setpassword($password);
	           	Mage::register('new_account',1);//Harapartners, Edward, Start of setting for Affliate email validation
	            $customer->save();
				Mage::unregister('new_account');//Harapartners, Edward, End of setting for Affliate email validation 
				Mage::dispatchEvent('customer_register_success',array('account_controller' => $this, 'customer' => $customer));// for affiliate customer tracking
        	}catch(Exception $e){
        		$result='account creation failed';
        	}
        	$customer->sendPasswordReminderEmail();//can be specifed email later.
        	$success = 'account created you will reveiv e a email including your temporal password';
        }
        if(!!$result){
        	$response->setBody($result);
        }
    }
    
    public function formatCode($code){
    	return preg_replace("/[^a-z0-9_]/", "_", trim(strtolower((urldecode($code)))));
    }
}