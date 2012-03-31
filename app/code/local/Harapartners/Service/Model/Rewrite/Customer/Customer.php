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
 
class Harapartners_Service_Model_Rewrite_Customer_Customer extends Mage_Customer_Model_Customer {
 
    const EXCEPTION_INVALID_STORE_ACCOUNT = 5;	//Harapartners, yang, multistore sigin error control

    public function authenticate($login, $password, $reValidate = false)
    {
        $this->loadByEmail($login);

        //Haraparnters, yang, START
		//Store switching, if customer has a valid account in another store/store view, redirect
        //If customer is invalid, stay in the current store/store view
    	if(!!$this->getStoreId()
    			&& $this->getStoreId() != Mage::app()->getStore()->getId()){
    		//Redirect
    		$urlObject = Mage::getModel('core/url')->setStore($this->getStoreId());
    		$url = $urlObject->getRouteUrl('customer/account/login');
			Mage::getSingleton('customer/session')->setBeforeAuthUrl($url);
			throw Mage::exception('Mage_Core', Mage::helper('customer')->__("Please login..."),
                self::EXCEPTION_INVALID_STORE_ACCOUNT
            );
        }
        //Haraparnters, yang, END
        if ($this->getConfirmation() && $this->isConfirmationRequired()) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('This account is not confirmed.'),
                self::EXCEPTION_EMAIL_NOT_CONFIRMED
            );
        }
        if (!$this->validatePassword($password)) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Invalid login or password.'),
                self::EXCEPTION_INVALID_EMAIL_OR_PASSWORD
            );
        }
        //Haraparnters, yang, Add param for re-validate
        if (!$reValidate){
        	Mage::dispatchEvent('customer_customer_authenticated', array(
		           'model'    => $this,
		           'password' => $password,
        	));
        }
		//Haraparnters, yang, Set 15min validation time
        Mage::getSingleton('customer/session')->setData('CUSTOMER_LAST_VALIDATION_TIME', now());
        return true;
    }
    
    
    //This is a very strange solution. Must check with the business logic
    //also there should also be a frontend validation so that the customer will be notified! 
    
    protected function _beforeSave(){
        parent::_beforeSave();

        $storeId = $this->getStoreId();
        if ($storeId === null) {
            $this->setStoreId(Mage::app()->getStore()->getId());
        }
        
        //Harapartners, remove alias for Gmail address, add by Jing Xiao
        $email = $this->_trimGmail($this->getEmail());
        $this->setEmail($email);
        

        $this->getGroupId();
        return $this;
    }
    
    /**
     * Haraparnters:
     * trim gmail address, to restore alias gmail address
     *
     * @param string $email
     * @return string original email
     */
    protected function _trimGmail($email) {
    	
    	$strArray = explode('@', $email);
    	
    	if(empty($strArray) ||
    	   empty($strArray[1]) ||
    	   $strArray[1] != 'gmail.com') {
    		return $email;
    	}
    	
		//get username, such as 'abcd'
		$username = $strArray[0];
		//Get username string's length
		$len = strlen($username);
		$originalGmail = '';
		
		//iterate chacrates in username string
		for($j=0; $j<$len; $j++) {
			//if encounters '+', discard the rest of the string
			if($username[$j] == '+') {
				break;
			}
			
			//check if it is '.', if yes, don't concatenate.
			if($username[$j] != '.') {
				//concatenate username chacrater
				$originalGmail .= $username[$j];
			}
		}
    	
		return $originalGmail . '@gmail.com';
    }

}
