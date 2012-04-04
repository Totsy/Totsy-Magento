<?php

class Harapartners_Paymentfactory_Model_Tokenize extends Mage_Cybersource_Model_Soap {
    
	protected $_code  = 'paymentfactory_tokenize';
    protected $_formBlockType = 'paymentfactory/form';
    protected $_infoBlockType = 'paymentfactory/info';
    protected $_payment = null;
    
    protected $_paymentProfile = null;

    
    // =============================================== //
    // =========== Magento payment work flow ========== //
    // =============================================== //
	public function getConfigPaymentAction(){
		if(!!$this->getData('forced_payment_action')){
			return $this->getData('forced_payment_action');
		}else{
    		return 'order';
		}
    	// parent::getConfigPaymentAction();
    }
    
	public function validate(){
		if(!!$this->getData('cybersource_subid')){
				return $this;
			}
		
		
		return parent::validate();        
    }
    
	public function order(Varien_Object $payment, $amount){
		//For Totsy, no payment is allowed to be captured upon order place
		$amount = 0.00;
     	$profile = $this->_getPaymentProfile($payment);
     	
     	if(!!$profile && !!$profile->getId()){
     		//For Totsy, all orders are captured, so the authorize is only used for validation
//     		if(!Mage::registry('is_current_payment_profile_just_created')){
     			$this->authorize($payment, $amount);
//     		}
     	}else{
     		$this->create($payment);
     		Mage::unregister('is_current_payment_profile_just_created');
     		Mage::register('is_current_payment_profile_just_created', true);
     	}
		
     	return $this;
		
		
//     	if (!!$payment->getData('cybersource_subid')){
//     		$profile = Mage::getModel('paymentfactory/profile')->loadByEncryptedSubscriptionId($payment->getData('cybersource_subid'));
//     		if(!!$profile && !!$profile->getEntityId()){
//     			//Replace encrypted to non-encrypted
//     			$payment->setData('cybersource_subid', $profile->getData('subscription_id'));
//     		}
//     		return $this->authorize($payment, $amount);
//     	}
//     	if (!!$payment->getData('cc_number')){
//     		$profile = Mage::getModel('paymentfactory/profile')->loadByCcNumberWithId($payment->getData('cc_number').$customerId);
//     		if(!!$profile && !!$profile->getId()){
//     			//Checkout with existing profile instead of creating new card
//     			$payment->setData('cybersource_subid', $profile->getData('cybersource_subid'));     			
//     			return $this->authorize($payment, $amount);
//     		}
//     	}
//     		
//        return $this->create($payment);
     }
     
	protected function iniRequest(){
		parent::iniRequest();
		$this->_addSubscriptionToRequest($this->_payment);
    }
     
    
    // ============================================== //
    // =========== Payment gateway actions ========== //
    // ============================================== //
    
    //-----------------Create Customer Payment Profile-------//
	public function create(Varien_Object $payment) {
		//??? can we use parent::authorize() with different init param ???
        $error = false;
        $soapClient = $this->getSoapApi();
        
        parent::iniRequest();

        $paySubscriptionCreateService = new stdClass();
        $paySubscriptionCreateService->run = "true";
        
        $this->_request->paySubscriptionCreateService = $paySubscriptionCreateService;    
        $this->addBillingAddress($payment->getOrder()->getBillingAddress(), $payment->getOrder()->getCustomerEmail());
        $this->addCcInfo($payment);
        
        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
        $this->_request->purchaseTotals = $purchaseTotals; 
         
        $subscription = new stdClass();
		$subscription->title  ="On-Demand Profile Test";
		$subscription->paymentMethod = "credit card";
		$this->_request->subscription = $subscription;
		
		$recurringSubscriptionInfo = new stdClass();
		$recurringSubscriptionInfo->frequency = "on-demand";
		$this->_request->recurringSubscriptionInfo = $recurringSubscriptionInfo;

        try {
            $result = $soapClient->runTransaction($this->_request);
            if ($result->reasonCode==self::RESPONSE_CODE_SUCCESS && $result->paySubscriptionCreateReply->reasonCode==self::RESPONSE_CODE_SUCCESS ) {
            	
                $payment->setLastTransId($result->requestID)
                    	->setCcTransId($result->requestID)
                    	->setIsTransactionClosed(0)
                    	->setCybersourceToken($result->requestToken)
                   	 	->setCcAvsStatus($result->ccAuthReply->avsCode);          	 	                  	 	
                /*
                 * checking if we have cvCode in response bc
                 * if we don't send cvn we don't get cvCode in response
                 */
                if (isset($result->ccAuthReply->cvCode)) {
                    $payment->setCcCidStatus($result->ccAuthReply->cvCode);
                }
            } else {
                 $error = Mage::helper('paymentfactory')->__('There is an error in processing the payment(create). Please try again or contact us.');
            }
        } catch (Exception $e) {
           Mage::throwException(
                Mage::helper('paymentfactory')->__('Gateway request error: %s', $e->getMessage())
            );
        }

        if ($error !== false) {
            Mage::throwException($error);
        }
        
        $payment->setCybersourceSubid($result->paySubscriptionCreateReply->subscriptionID);
        try{
        	$customerId = $payment->getOrder()->getQuote()->getCustomerId();
        	$data = new Varien_Object($payment->getData());
        	$data->setData('customer_id', $customerId);
        	$profile = Mage::getModel('paymentfactory/profile');
        	$profile->importDataWithValidation($data);
        	
			// ===================================================================================== //
        	//WARNING! The current process is within order processing DB transaction, no DB write will be committed until the very end!!!
        	$profile->save();
        	// ===================================================================================== //
        	
        	Mage::unregister('current_payment_profile');
        	Mage::register('current_payment_profile', $profile);
        	
        }catch (Exception $e) {
           Mage::throwException(
                Mage::helper('paymentfactory')->__('Can not save payment profile')
            );
        }
        
        return $this;
    }

    public function authorize(Varien_Object $payment, $amount){
    	$this->_payment = $payment;
    	$profile = $this->_getPaymentProfile($payment);
    	
    	//Add data for processing
		$payment->setData('cybersource_subid', $profile->getData('subscription_id'));
		//Add data for display    
    	$payment->setCcLast4($profile->getData('last4no'));
	    $payment->setCcType($profile->getData('card_type'));
	    $payment->setCcExpYear($profile->getData('expire_year'));
	    $payment->setCcExpMonth($profile->getData('expire_month'));
	    
    	try{
    		parent::authorize($payment, $amount);

    		
    		//Reset profile fail count
    		if($profile->getFailedCount() > 0){
    			$profile->setFailedCount(0);
    			$profile->save();
    		}
    	}catch (Exception $e){
    		$profile->setFailedCount($profile->getFailedCount()+1);
    		$profile->save();
    		//Important, exception must keep bubbling
    		throw $e;
    	}
    	$this->_payment = NULL; 
        return $this;
    }

    public function capture(Varien_Object $payment, $amount){
    	$this->_payment = $payment;
    	$profile = $this->_getPaymentProfile($payment);
    	
    	//Add data for processing
		$payment->setData('cybersource_subid', $profile->getData('subscription_id'));
		//Add data for display    
    	$payment->setCcLast4($profile->getData('last4no'));
	    $payment->setCcType($profile->getData('card_type'));
	    $payment->setCcExpYear($profile->getData('expire_year'));
	    $payment->setCcExpMonth($profile->getData('expire_month'));
	    
    	
    	try{
	        parent::capture($payment, $amount);
    		//Reset profile fail count
    		if($profile->getFailedCount() > 0){
    			$profile->setFailedCount(0);
    			$profile->save();
    		}
    	}catch (Exception $e){
    		$profile->setFailedCount($profile->getFailedCount()+1);
    		$profile->save();
    		//Important, exception must keep bubbling
    		throw $e;
    	}
        $this->_payment = NULL;
        return $this;
    }

    // ========================================== //
	// ============= Utilities ================== //
	// ========================================== //
	
    protected function _getPaymentProfile($payment){
    	//Object internal cache first
    	if($this->_paymentProfile instanceof Harapartners_Paymentfactory_Model_Profile
    			&& !!$this->_paymentProfile->getId()){
    		return $this->_paymentProfile;
    	}
    	
    	
    	//Else, Existing profile always has the highest priority
    	//This is important since objects cannot be saved during the order processing steps (within one DB transaction)
    	if(!!($profile = Mage::registry('current_payment_profile'))
    			&& $profile instanceof Harapartners_Paymentfactory_Model_Profile
    			&& !!$profile->getId()){
    		return $this->_paymentProfile = $profile;
    	}
    	
    	//Otherwise, try to load existing profile here
		$this->_paymentProfile = Mage::getModel('paymentfactory/profile');
		if(!!$payment->getData('cybersource_subid')){
			$this->_paymentProfile->loadByEncryptedSubscriptionId($payment->getData('cybersource_subid'));
			if(!$this->_paymentProfile || !$this->_paymentProfile->getId()){
				throw new Exception('Invalid profile, please try a different card.');
			}
		}elseif (!!$payment->getData('cc_number')){
			//Check against creating duplicate profile of the same credit card + customer ID
			$customerId = $payment->getOrder()->getQuote()->getCustomerId();
     		$this->_paymentProfile->loadByCcNumberWithId($payment->getData('cc_number').$customerId);
     	}
     	
     	return $this->_paymentProfile;
    }

    protected function addCcInfo($payment){

    	if (!!$payment->getData('cybersource_subid')){
     				return;
     	}else{
     			
        	$card = new stdClass();
        	$card->fullName = $payment->getCcOwner();
        	$card->accountNumber = $payment->getCcNumber();
        	$card->expirationMonth = $payment->getCcExpMonth();
        	$card->expirationYear =  $payment->getCcExpYear();
        
       	 	$card->cardType = $this->getTypeNumber($payment->getCcType()) ;
        
        
        	if ($payment->hasCcCid()) {
            	$card->cvNumber =  $payment->getCcCid();
        	}
        	if ($payment->getCcType()==self::CC_CARDTYPE_SS && $payment->hasCcSsIssue()) {
            	$card->issueNumber =  $payment->getCcSsIssue();
        	}
        	if ($payment->getCcType()==self::CC_CARDTYPE_SS && $payment->hasCcSsStartYear()) {
            	$card->startMonth =  $payment->getCcSsStartMonth();
            	$card->startYear =  $payment->getCcSsStartYear();
        	}
        	$this->_request->card = $card;
    	}
    }
   
	public function getTypeNumber( $type ) {
    	switch ( $type ) {
    		case 'VI':
	 			return '001';
	 		case 'AE':
	 			return '003';
	 		case 'MC':
	 			return '002';
	 		case 'DI':
	 			return '004';
	 		default:
	 			return '000';
	 	}
    }
    
    
	protected function _addSubscriptionToRequest($payment){
        $subscription = new stdClass();
		$subscription->title  ="On-Demand Profile Test";
		$subscription->paymentMethod = "credit card";
		$this->_request->subscription = $subscription;
	
		$recurringSubscriptionInfo = new stdClass();
		$recurringSubscriptionInfo->frequency = "on-demand";
		$recurringSubscriptionInfo->subscriptionID = $payment->getCybersourceSubid();
		$this->_request->recurringSubscriptionInfo = $recurringSubscriptionInfo;
	}
    
}