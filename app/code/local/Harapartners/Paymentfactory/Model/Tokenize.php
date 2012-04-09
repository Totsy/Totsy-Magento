<?php

class Harapartners_Paymentfactory_Model_Tokenize extends Mage_Cybersource_Model_Soap {
    
	protected $_code  = 'paymentfactory_tokenize';
    protected $_formBlockType = 'paymentfactory/form';
    protected $_infoBlockType = 'paymentfactory/info';
    protected $_payment = null;

    
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
		//For totsy, no payment is allowed to be captured upon order place
		$amount = 0.00;
		$customerId = $payment->getOrder()->getQuote()->getCustomerId();
		
     	if (!!$payment->getData('cybersource_subid')){
//     		$profile = Mage::getModel('paymentfactory/profile')->loadByEncryptedSubscriptionId($payment->getData('cybersource_subid'));
//     		if(!!$profile && !!$profile->getEntityId()){
//     			//Replace encrypted to non-encrypted
//     			$payment->setData('cybersource_subid', $profile->getData('subscription_id'));
//     		}
     		return $this->authorize($payment, $amount);
     	}
     	if (!!$payment->getData('cc_number')){
     		$profile = Mage::getModel('paymentfactory/profile')->loadByCcNumberWithId($payment->getData('cc_number').$customerId);
     		if(!!$profile && !!$profile->getId() && $profile->getExpireYear() === $payment->getCcExpYear()  && $profile->getExpireMonth() === $payment->getCcExpMonth() ){
     			//Checkout with existing profile instead of creating new card
     			$payment->setData('cybersource_subid', $profile->getData('cybersource_subid'));     			
     			return $this->authorize($payment, $amount);
     		}
     	}
     		
        return $this->create($payment);
     }
     
	protected function iniRequest(){
		parent::iniRequest();
		$this->_addSubscriptionToRequest($this->_payment);
		
		//Harapartners, Jun, Totsy logic requires Order ID when applicable
		$order = $this->getInfoInstance()->getOrder();
		if(!!$order && !!$order->getData('increment_id')){
			$this->_request->merchantReferenceCode = $order->getData('increment_id');
		}
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
        	$data->setData('cybersource_sudid', $result->paySubscriptionCreateReply->subscriptionID);
        	$profile = Mage::getModel('paymentfactory/profile');
        	$profile->importDataWithValidation($data);               
        	$profile->save();
        }catch (Exception $e) {
           Mage::throwException(
                Mage::helper('paymentfactory')->__('Can not save payment profile')
            );
        }
        
        return $this;
    }

    public function authorize(Varien_Object $payment, $amount){
    	$this->_payment = $payment;
    	parent::authorize($payment, $amount);
    	$payment->setCybersourceSubid($payment->getCybersourceSubid());
    	$profile = Mage::getModel('paymentfactory/profile')->loadBySubscriptionId($payment->getCybersourceSubid());
    	$payment->setCcLast4($profile->getData('last4no'));
    	$payment->setCcType($profile->getData('card_type'));
    	$payment->setCcExpYear($profile->getData('expire_year'));
    	$payment->setCcExpMonth($profile->getData('expire_month'));
    	
    	$this->_payment = NULL; 
        return $this;
    }

    public function capture(Varien_Object $payment, $amount){
    	$this->_payment = $payment;
        parent::capture($payment, $amount);
        $payment->setCybersourceSubid($payment->getCybersourceSubid()); //Harapartners
        $profile = Mage::getModel('paymentfactory/profile')->loadBySubscriptionId($payment->getCybersourceSubid());
    	$payment->setCcLast4($profile->getData('last4no'));
    	$payment->setCcType($profile->getData('card_type'));
    	$payment->setCcExpYear($profile->getData('expire_year'));
    	$payment->setCcExpMonth($profile->getData('expire_month'));
        $this->_payment = NULL;
        return $this;
    }

    // ========================================== //
	// ============= Utilities ================== //
	// ========================================== //

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
	 			return 000;
	 	}
    }
    
    
	protected function _addSubscriptionToRequest($payment){
		//For refund we do NOT need subscription info, $payment will be null
		if(!!$payment){
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
    
}