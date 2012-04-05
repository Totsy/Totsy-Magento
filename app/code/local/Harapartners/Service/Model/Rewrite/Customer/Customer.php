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
    
    //Harapartners, Jun, Important logic to handle legacy customers
	public function validatePassword($password){
		if(!!$this->getData('legacy_customer')){
			return (sha1($password) == $this->getPasswordHash());
		}else{
			return parent::validatePassword($password);
		}
    }
    
	public function setPassword($password){
		parent::setPassword($password);
        if(!!$this->getData('legacy_customer')){
	        $this->setData('legacy_customer', 0);
	        $this->_getResource()->saveAttribute($this, 'legacy_customer');
        }
        return $this;
    }
    
//    //Harapartners, Jun, Legacy customer will be come concurrent after password change
//	public function changePassword($newPassword) {
//		parent::changePassword($newPassword);
//        if(!!$this->getData('legacy_customer')){
//	        $this->setData('legacy_customer', 0);
//	        $this->_getResource()->saveAttribute($this, 'legacy_customer');
//        }
//        return $this;
//    }
//    
//    //Harapartners, Jun, ForgotPassWord logic does NOT route via changePassword($newPassword)
//	public function changeResetPasswordLinkToken($newResetPasswordLinkToken) {
//        parent::changeResetPasswordLinkToken($newResetPasswordLinkToken);
//		if(!!$this->getData('legacy_customer')){
//	        $this->setData('legacy_customer', 0);
//	        $this->_getResource()->saveAttribute($this, 'legacy_customer');
//        }
//        return $this;
//    }

    public function authenticate($login, $password, $reValidate = false) {
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
    
    //Haraparnters, jun: remove first name last name validation from registering
	public function validate(){
        $errors = array();
        $customerHelper = Mage::helper('customer');

        if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = $customerHelper->__('Invalid email address "%s".', $this->getEmail());
        }

        $password = $this->getPassword();
        if (!$this->getId() && !Zend_Validate::is($password , 'NotEmpty')) {
            $errors[] = $customerHelper->__('The password cannot be empty.');
        }
        if (strlen($password) && !Zend_Validate::is($password, 'StringLength', array(6))) {
            $errors[] = $customerHelper->__('The minimum password length is %s', 6);
        }
        $confirmation = $this->getConfirmation();
        if ($password != $confirmation) {
            $errors[] = $customerHelper->__('Please make sure your passwords match.');
        }

        $entityType = Mage::getSingleton('eav/config')->getEntityType('customer');
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'dob');
        if ($attribute->getIsRequired() && '' == trim($this->getDob())) {
            $errors[] = $customerHelper->__('The Date of Birth is required.');
        }
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'taxvat');
        if ($attribute->getIsRequired() && '' == trim($this->getTaxvat())) {
            $errors[] = $customerHelper->__('The TAX/VAT number is required.');
        }
        $attribute = Mage::getModel('customer/attribute')->loadByCode($entityType, 'gender');
        if ($attribute->getIsRequired() && '' == trim($this->getGender())) {
            $errors[] = $customerHelper->__('Gender is required.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
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
		$trimmedGmail = '';
		
		//iterate chacrates in username string
		for($j=0; $j<$len; $j++) {
			//if encounters '+', discard the rest of the string
			if($username[$j] == '+') {
				break;
			}
			
			//check if it is '.', if yes, don't concatenate.
			if($username[$j] != '.') {
				//concatenate username chacrater
				$trimmedGmail .= $username[$j];
			}
		}
		
		$trimmedGmail .= '@gmail.com';
		
		if($email != $trimmedGmail) {
			//if gmail has been trimmed, show message.
			$message = 'Your Gmail address alias(' . $email . ') has been trimmed as actual Gmail address(' . $trimmedGmail . ').';
			Mage::getSingleton('customer/session')->addError(Mage::helper('customer')->__($message));
		}

		return $trimmedGmail;
    }

}
