<?php

class Harapartners_Paymentfactory_Model_Tokenize extends Mage_Cybersource_Model_Soap {
    
    const MINIMUM_AUTHORIZE_AMOUNT = 1.0;
    
    protected $_code  = 'paymentfactory_tokenize';
    protected $_formBlockType = 'paymentfactory/form';
    protected $_infoBlockType = 'paymentfactory/info';
    protected $_payment = null;
    protected $_canRefundInvoicePartial = true;
    
    //HP, Payment failed emails (to customer and admin)
    protected $_emailTemplate;
    protected $_sender;
    protected $_adminReceivers;
    
	public function __construct(){
		$this->_prepareEmail();
		return parent::__construct();
    }
    
    protected function _prepareEmail(){
    	//HP, load config values
    	$this->_emailTemplate = Mage::getStoreConfig('checkout/payment_failed/template');
    	$this->_sender = Mage::getStoreConfig('checkout/payment_failed/identity');
    	
    	//Different code should go to different admin, always copy to master admin
    	$receiverCode = Mage::getStoreConfig('checkout/payment_failed/reciever');
		$receiverEmail = 'trans_email/ident_'.$receiverCode.'/email';
		$this->_adminReceivers = array(Mage::getStoreConfig($receiverEmail), Mage::getStoreConfig('checkout/payment_failed/copy_to'));
		return;
    }
    
    protected function _sendPaymentFailedEmail($payment){
    	
        	$objectsArray = array('order' => $payment->getOrder());	
        	//To customer and also admin
           	Mage::getModel('core/email_template')->setTemplateSubject('Payment Failed')->sendTransactional(
					$this->_emailTemplate, 
					$this->_sender, 
					array_merge(array($payment->getOrder()->getCustomerEmail()), $this->_adminReceivers), 
					'', 
					$objectsArray, 
					$this->getStore()
			);
    }

    
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
    
    protected function _decryptSubscriptionId($subId){
        try{
            $testSubId = Mage::getModel('core/encryption')->decrypt(base64_decode($subId));
            if(is_numeric($testSubId)){
                $subId = $testSubId;
            }
        }catch (Exception $e){
        }
        return $subId;
    }
    
    public function order(Varien_Object $payment, $amount){
        //For totsy, no payment is allowed to be captured upon order place
        $customerId = $payment->getOrder()->getQuote()->getCustomerId();
        
        $profile = Mage::getModel('paymentfactory/profile');
         if (!!$payment->getData('cybersource_subid')){
             //decrypt for the backend
	         $subscriptionId = $this->_decryptSubscriptionId($payment->getData('cybersource_subid'));
	         if(!!$subscriptionId){
	            $payment->setData('cybersource_subid', $subscriptionId);
	         }
             $profile->loadBySubscriptionId($payment->getData('cybersource_subid'));
         }elseif (!!$payment->getData('cc_number')){
             $profile->loadByCcNumberWithId($payment->getData('cc_number').$customerId.$payment->getCcExpYear().$payment->getCcExpMonth());
         }
         
        if(!!$profile && !!$profile->getId() 
            // $profile->getExpireYear() === $payment->getCcExpYear() 
            // $profile->getExpireMonth() === $payment->getCcExpMonth()
        ){
            $profile->setIsDefault(0);
            $profile->save();
             //Checkout with existing profile instead of creating new card
             //$payment->setData('cybersource_subid', $profile->getData('subscription_id'));                 
             return $this->_validateProfile($profile, $payment);
         }
             
        return $this->create($payment);
     }
     
     protected function _validateProfile($profile, $payment){
//         IF VISA, auth 0
//         ELSE auth 1 and void
        if ($profile->getCardType() == 'VI'){
            return $this->authorize($payment, 0.0);
        }elseif ($profile->getCardType() == 'AE'){
            //Amex does not allow Authorization Reversal at this moment
            return $this->authorize($payment, self::MINIMUM_AUTHORIZE_AMOUNT);
        }else{
            $validationStatus = $this->authorize($payment, self::MINIMUM_AUTHORIZE_AMOUNT);
            if($validationStatus){
                $payment->setParentTransactionId($payment->getTransactionId());
                $this->voidSpecial($payment, self::MINIMUM_AUTHORIZE_AMOUNT);
            }
            return $validationStatus;
        }
         
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
        	
      		$order = $payment->getOrder();
        	$order->setStatus(Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED);
        	$this->_sendPaymentFailedEmail($payment);
        	        	        	
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
            Mage::getModel('core/email_template')->setTemplateSubject('Payment Failed')
                            ->sendTransactional(6, 'support@totsy.com', $payment->getOrder()->getCustomerEmail(), $payment->getOrder()->getCustomer()->getFirstname());
        
           Mage::throwException(
                Mage::helper('paymentfactory')->__('Can not save payment profile')
            );
        }
        
        return $this;
    }

    public function authorize(Varien_Object $payment, $amount){
        $this->_payment = $payment;
        try{
        	parent::authorize($payment, $amount);      	
        	
        }catch (Exception $e){
        	
        	$order = $payment->getOrder();
        	$order->setStatus(Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED);
        	$this->_sendPaymentFailedEmail($payment);
        	
			Mage::throwException(
                Mage::helper('cybersource')->__('Gateway request error: %s', $e->getMessage())
            );
        }
        
        $payment->setCybersourceSubid($payment->getCybersourceSubid());
        $profile = Mage::getModel('paymentfactory/profile')->loadBySubscriptionId($payment->getCybersourceSubid());
        $payment->setCcLast4($profile->getData('last4no'));
        $payment->setCcType($profile->getData('card_type'));
        $payment->setCcExpYear($profile->getData('expire_year'));
        $payment->setCcExpMonth($profile->getData('expire_month'));
        $this->_payment = NULL; 
        return $this;
    }
    
    public function void(Varien_Object $payment, $amount){
        $this->_payment = $payment;
        parent::void($payment, $amount);
        $payment->setCybersourceSubid($payment->getCybersourceSubid()); //Harapartners
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
        try {
        	parent::capture($payment, $amount);
        }catch (Exception $e){
        	
        	$order = $payment->getOrder();
        	$order->setStatus(Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED)->save();
        	$this->_sendPaymentFailedEmail($payment);
        	
			Mage::throwException(
                Mage::helper('cybersource')->__('Gateway request error: %s', $e->getMessage())
            );
        }
        
        $payment->setCybersourceSubid($payment->getCybersourceSubid()); //Harapartners
        $profile = Mage::getModel('paymentfactory/profile')->loadBySubscriptionId($payment->getCybersourceSubid());
        $payment->setCcLast4($profile->getData('last4no'));
        $payment->setCcType($profile->getData('card_type'));
        $payment->setCcExpYear($profile->getData('expire_year'));
        $payment->setCcExpMonth($profile->getData('expire_month'));
        $this->_payment = NULL;
        return $this;
    }
    
    public function voidSpecial(Varien_Object $payment, $amount){ //for void $1 as V,Harapartners
        $this->_payment = $payment;
        $error = false;
        if ($payment->getTransactionId() && $payment->getCybersourceToken()) {
            $soapClient = $this->getSoapApi();
            
            parent::iniRequest();
            
//            $ccAuthReversalService = new stdClass();
//            $ccAuthReversalService->run = "true";
//            $ccAuthReversalService->authRequestID = $payment->getTransactionId();
//            $ccAuthReversalService->authRequestToken = $payment->getCybersourceToken();
//            $this->_request->ccAuthReversalService = $ccAuthReversalService;

//             $voidService
            $voidService = new stdClass();
            $voidService->run = "true";
            $voidService->authRequestID = $payment->getTransactionId();
            $voidService->authRequestToken = $payment->getCybersourceToken();
            $this->_request->voidService = $voidService;
             
            $purchaseTotals = new stdClass();
            $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
            $purchaseTotals->grandTotalAmount = $amount;
            $this->_request->purchaseTotals = $purchaseTotals;

            try {
                $result = $soapClient->runTransaction($this->_request);
                if ($result->reasonCode==self::RESPONSE_CODE_SUCCESS) {
                    $payment->setTransactionId($result->requestID)
                        ->setCybersourceToken($result->requestToken);
                     //   ->setIsTransactionClosed(1);
                } else {
                     $error = Mage::helper('cybersource')->__('There is an error in processing the payment. Please try again or contact us.');
                }
            } catch (Exception $e) {
               Mage::throwException(
                    Mage::helper('cybersource')->__('Gateway request error: %s', $e->getMessage())
                );
            }
        }else{
            $error = Mage::helper('cybersource')->__('Invalid transaction id or token');
        }
        if ($error !== false) {
            Mage::throwException($error);
        }
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
             default:
                 return 000;
         }
    }

    protected function _addSubscriptionToRequest($payment){
        //For refund we do NOT need subscription info, and $payment will be null
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
    
    
    public function createProfile($payment,$billing,$customerId) {
                
        //??? can we use parent::authorize() with different init param ???
        $error = false;
        $soapClient = $this->getSoapApi();
        
        parent::iniRequest();

        $paySubscriptionCreateService = new stdClass();
        $paySubscriptionCreateService->run = "true";
        
        $this->_request->paySubscriptionCreateService = $paySubscriptionCreateService;    
        
        $billTo = new stdClass();
        $billTo->firstName = $billing->getFirstname();
        $billTo->lastName = $billing->getLastname();
        $billTo->company = $billing->getCompany();
        $billTo->street1 = $billing->getStreet(0);
        $billTo->street2 = $billing->getStreet(1);
        $billTo->city = $billing->getCity();
        $billTo->state = $billing->getRegion();
        $billTo->postalCode = $billing->getPostcode();
        $billTo->country = 'US';
        $billTo->phoneNumber = $billing->getTelephone();
        $billTo->email = ($email ? $email : Mage::getStoreConfig('trans_email/ident_general/email'));
        $billTo->ipAddress = $this->getIpAddress();
        $this->_request->billTo = $billTo;        
        
        $this->addCcInfo($payment);
        
        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = 'USD';
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
                 $error = Mage::helper('paymentfactory')->__('There is an gateway error in processing the payment(create). Please try again or contact us.');
            }
        } catch (Exception $e) {
        	
        	$order = $payment->getOrder();
        	$order->setStatus(Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED);
        	$this->_sendPaymentFailedEmail($payment);
        	
           Mage::throwException(
                Mage::helper('paymentfactory')->__('Gateway request error: %s', $e->getMessage())
            );
        }

        if ($error !== false) {
            Mage::throwException($error);
        }
        
        $payment->setCybersourceSubid($result->paySubscriptionCreateReply->subscriptionID);
        try{
            $data = new Varien_Object($payment->getData());
            $data->setData('customer_id', $customerId);
            $data->setData('cc_last4', substr($payment->getCcNumber(), -4));
            $data->setData('cybersource_subid', $result->paySubscriptionCreateReply->subscriptionID);
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
    
}