<?php
/**
 * @category    Totsy
 * @package     Totsy_Cybersource_Model
 * @author      troyer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Cybersource_Model_Soap extends Mage_Cybersource_Model_Soap
{
   protected $_code  = 'cybersource_soap';
    protected $_formBlockType = 'cybersource/form';
    protected $_infoBlockType = 'cybersource/info';

    const WSDL_URL_TEST = 'https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.26.wsdl';
    const WSDL_URL_LIVE = 'https://ics2ws.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.26.wsdl';

    const RESPONSE_CODE_SUCCESS = 100;

    const CC_CARDTYPE_SS = 'SS';

    /**
     * Availability options
    */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc = false;

    protected $_request;

    protected $_errors = array(
        -1 => 'Unknown error code',
        101 => 'The request is missing one or more required fields.',
        102 => 'One or more fields in the request contains invalid data',
        110 => 'Only a partial amount was approved.',
        150 => 'Error: General system failure.',
        151 => 'Error: The request was received but there was a server timeout.',
        152 => 'Error: The request was received, but a service did not finish running in time.',
        200 => 'The authorization request was approved by the issuing bank but declined by
            CyberSource because it did not pass the Address Verification System (AVS) check.',
        201 => 'The issuing bank has questions about the request. You do not receive an
            authorization code programmatically, but you might receive one verbally by calling
            the processor.',
        202 => 'Either the card provided is expired or the date provided is incorrect.',
        203 => 'General decline of the card. No other information provided by the issuing bank.',
        204 => 'Insufficient funds in the account.',
        205 => 'This card has been reported stolen or lost.',
        207 => 'The issuing bank for this card is currently not available. Try again soon.',
        208 => 'Either the card is inactive or not authorized for card-not-present transactions.',
        209 => 'The American Express Card Identification Digits (CID) did not match.',
        210 => 'The provided card has reached it\'s credit limit.',
        211 => 'Invalid CVN',
        221 => 'The customer matched an entry on the processor\'s negative file.',
        230 => 'The authorization request was approved by the issuing bank but declined by
            CyberSource because it did not pass the CVN check.',
        231 => 'An invalid account number was provided.',
        232 => 'The card type provided is not accepted by the payment processor.',
        233 => 'General decline by the payment processor.',
        234 => 'There is a problem with the information in your CyberSource account,
            the account holder should contact CyberSource customer support.',
        235 => 'The requested capture amount exceeds the originally authorized amount.',
        236 => 'An unknown processor failure occured.',
        237 => 'The authorization has already been reversed.',
        238 => 'The authorization has already been captured.',
        239 => 'The requested transaction amount must match the previous transaction amount.',
        240 => 'The card type sent is invalid or does not correlate with the credit card number.',
        241 => 'The request ID is invalid',
        242 => 'You requested a capture, but there is no corresponding,
            unused authorization record. Occurs if there was not a previously successful
            authorization request or if the previously successful authorization has already been
            used by another capture request.',
        243 => 'The transaction has already been settled or reversed.',
        246 => 'The capture or credit is not voidable because it has already been submitted to
            your processor or this transaction cannot be voided.',
        247 => 'You requested a credit for a capture that was previously voided.',
        250 => 'Error: Thr request was received but there was a timeout at the payment processor.'
    );
        /*
    * overwrites the method of Mage_Payment_Model_Method_Cc
    * for switch or solo card
    */
    public function OtherCcType($type)
    {
        return (parent::OtherCcType($type) || $type==self::CC_CARDTYPE_SS || $type=='JCB' || $type=='UATP');
    }

    /**
     * overwrites the method of Mage_Payment_Model_Method_Cc
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {

        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        parent::assignData($data);
        $info = $this->getInfoInstance();

        if ($data->getCcType()==self::CC_CARDTYPE_SS) {
            $info->setCcSsIssue($data->getCcSsIssue())
                ->setCcSsStartMonth($data->getCcSsStartMonth())
                ->setCcSsStartYear($data->getCcSsStartYear())
            ;
        }
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate()
    {    //return $this;
        
        if (!extension_loaded('soap')) {
            Mage::throwException(Mage::helper('cybersource')->__('SOAP extension is not enabled. Please contact us.'));
        }
        /**
        * to validate paymene method is allowed for billing country or not
        */
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $billingCountry = $paymentInfo->getOrder()->getBillingAddress()->getCountryId();
        } else {
            $billingCountry = $paymentInfo->getQuote()->getBillingAddress()->getCountryId();
        }
        if (!$this->canUseForCountry($billingCountry)) {
            Mage::throwException($this->_getHelper()->__('Selected payment type is not allowed for billing country.'));
        }

        $info = $this->getInfoInstance();
        $errorMsg = false;
        $availableTypes = explode(',',$this->getConfigData('cctypes'));

        $ccNumber = $info->getCcNumber();

        // remove credit card number delimiters such as "-" and space
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $info->setCcNumber($ccNumber);

        $ccType = '';

        if (!$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
            $errorCode = 'ccsave_expiration,ccsave_expiration_yr';
            $errorMsg = $this->_getHelper()->__('Incorrect credit card expiration date.');
        }

        if (in_array($info->getCcType(), $availableTypes)){
            if ($this->validateCcNum($ccNumber)
                // Other credit card type number validation
                || ($this->OtherCcType($info->getCcType()) && $this->validateCcNumOther($ccNumber))) {

                $ccType = 'OT';
                $ccTypeRegExpList = array(
                    'SO' => '/(^(6334)[5-9](\d{11}$|\d{13,14}$))|(^(6767)(\d{12}$|\d{14,15}$))/', // Solo only
                    'SM' => '/(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))/',//Maestro/Switch
                    'VI' => '/^4[0-9]{12}([0-9]{3})?$/', // Visa
                    'MC' => '/^5[1-5][0-9]{14}$/',       // Master Card
                    'AE' => '/^3[47][0-9]{13}$/',        // American Express
                    'DI' => '/^6011[0-9]{12}$/',          // Discovery
                    'JCB' => '/^(3[0-9]{15}|(2131|1800)[0-9]{12})$/', // JCB
                    'LASER' => '/^(6304|6706|6771|6709)[0-9]{12}([0-9]{3})?$/' // LASER
                );

                foreach ($ccTypeRegExpList as $ccTypeMatch=>$ccTypeRegExp) {
                    if (preg_match($ccTypeRegExp, $ccNumber)) {
                        $ccType = $ccTypeMatch;
                        break;
                    }
                }

                if (!$this->OtherCcType($info->getCcType()) && $ccType!=$info->getCcType()) {
                    $errorCode = 'ccsave_cc_type,ccsave_cc_number';
                    $errorMsg = $this->_getHelper()->__('Credit card number mismatch with credit card type.');
                }
            }
            else {
                $errorCode = 'ccsave_cc_number';
                $errorMsg = $this->_getHelper()->__('Invalid Credit Card Number');
            }

        }
        else {
            $errorCode = 'ccsave_cc_type';
            $errorMsg = $this->_getHelper()->__('Credit card type is not allowed for this payment method.');
        }

                                //validate credit card verification number
        if ($errorMsg === false && $this->hasVerification()) {
            $verifcationRegEx = $this->getVerificationRegEx();
            $regExp = isset($verifcationRegEx[$info->getCcType()]) ? $verifcationRegEx[$info->getCcType()] : '';
            if (!$info->getCcCid() || !$regExp || !preg_match($regExp ,$info->getCcCid())){
                $errorMsg = $this->_getHelper()->__('Please enter a valid credit card verification number.');
            }
        }

        if($errorMsg){
            Mage::throwException($errorMsg);
        }
        return $this;
    }

   /**
     * Getting Soap Api object
     *
     * @param   array $options
     * @return  Mage_Cybersource_Model_Api_ExtendedSoapClient
     */
    protected function getSoapApi($options = array())
    {
        $wsdl = $this->getConfigData('test') ? self::WSDL_URL_TEST  : self::WSDL_URL_LIVE;
        $_api = new Mage_Cybersource_Model_Api_ExtendedSoapClient($wsdl, $options);
        $_api->setStoreId($this->getStore());
        return $_api;
    }

    /**
     * Initializing soap header
     */
    protected function iniRequest()
    {
        $this->_request = new stdClass();
        $this->_request->merchantID = $this->getConfigData('merchant_id');
        $this->_request->merchantReferenceCode = $this->_generateReferenceCode();

        $this->_request->clientLibrary = "PHP";
        $this->_request->clientLibraryVersion = phpversion();
        $this->_request->clientEnvironment = php_uname();
    }

    /**
     * Random generator for merchant referenc code
     *
     * @return random number
     */
    protected function _generateReferenceCode()
    {
        return Mage::helper('core')->uniqHash();
    }

    /**
     * Getting customer IP address
     *
     * @return IP address string
     */
    protected function getIpAddress()
    {
        return Mage::helper('core/http')->getRemoteAddr();
    }

    /**
     * Assigning billing address to soap
     *
     * @param Varien_Object $billing
     * @param String $email
     */
    protected function addBillingAddress($billing, $email)
    {
        if (!$email) {
            $email = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getEmail();
        }
        $billTo = new stdClass();
        $billTo->firstName = $billing->getFirstname();
        $billTo->lastName = $billing->getLastname();
        $billTo->company = $billing->getCompany();
        $billTo->street1 = $billing->getStreet(1);
        $billTo->street2 = $billing->getStreet(2);
        $billTo->city = $billing->getCity();
        $billTo->state = $billing->getRegion();
        $billTo->postalCode = $billing->getPostcode();
        $billTo->country = $billing->getCountry();
        $billTo->phoneNumber = $billing->getTelephone();
        $billTo->email = ($email ? $email : Mage::getStoreConfig('trans_email/ident_general/email'));
        $billTo->ipAddress = $this->getIpAddress();
        $this->_request->billTo = $billTo;
    }

    /**
     * Assigning shipping address to soap object
     *
     * @param Varien_Object $shipping
     */
    protected function addShippingAddress($shipping)
    {
        //checking if we have shipping address, in case of virtual order we will not have it
        if ($shipping) {
            $shipTo = new stdClass();
            $shipTo->firstName = $shipping->getFirstname();
            $shipTo->lastName = $shipping->getLastname();
            $shipTo->company = $shipping->getCompany();
            $shipTo->street1 = $shipping->getStreet(1);
            $shipTo->street2 = $shipping->getStreet(2);
            $shipTo->city = $shipping->getCity();
            $shipTo->state = $shipping->getRegion();
            $shipTo->postalCode = $shipping->getPostcode();
            $shipTo->country = $shipping->getCountry();
            $shipTo->phoneNumber = $shipping->getTelephone();
            $this->_request->shipTo = $shipTo;
        }
    }

    /**
     * Assigning credit card information
     *
     * @param Mage_Model_Order_Payment $payment
     */
    protected function addCcInfo($payment)
    {
        $card = new stdClass();
        $card->fullName = $payment->getCcOwner();
        $card->accountNumber = $payment->getCcNumber();
        $card->expirationMonth = $payment->getCcExpMonth();
        $card->expirationYear =  $payment->getCcExpYear();
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

    /**
     * Authorizing payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Cybersource_Model_Soap
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $error = false;

        $soapClient = $this->getSoapApi();

        $this->iniRequest();

        $ccAuthService = new stdClass();
        $ccAuthService->run = "true";
        $this->_request->ccAuthService = $ccAuthService;
        $this->addBillingAddress($payment->getOrder()->getBillingAddress(), $payment->getOrder()->getCustomerEmail());
        $this->addShippingAddress($payment->getOrder()->getShippingAddress());
        $this->addCcInfo($payment);

        $purchaseTotals = new stdClass();
        $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
        $purchaseTotals->grandTotalAmount = $amount;
        $this->_request->purchaseTotals = $purchaseTotals;
        try {
            $result = $soapClient->runTransaction($this->_request);
            if ($result->reasonCode==self::RESPONSE_CODE_SUCCESS) {
                $payment->setLastTransId($result->requestID)
                    ->setCcTransId($result->requestID)
                    ->setTransactionId($result->requestID)
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
                 $error = Mage::helper('cybersource')->__('There is an error in processing the payment. ' . $this->_errors[$result->reasonCode] . ' Please try again or contact us.');
            }
        } catch (Exception $e) {
            Mage::throwException(
                Mage::helper('cybersource')->__('Gateway request error: %s', $e->getMessage())
            );
        }

        if ($error !== false) {
            Mage::throwException($error);
        }
        return $this;
    }

    /**
     * Capturing payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Cybersource_Model_Soap
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $error = false;
        $soapClient = $this->getSoapApi();
        $this->iniRequest();

        if ($payment->getParentTransactionId() && $payment->getCybersourceToken()) {
            $ccCaptureService = new stdClass();
            $ccCaptureService->run = "true";
            $ccCaptureService->authRequestToken = $payment->getCybersourceToken();
            $ccCaptureService->authRequestID = $payment->getParentTransactionId();
            $this->_request->ccCaptureService = $ccCaptureService;

            $item0 = new stdClass();
            $item0->unitPrice = $amount;
            $item0->id = 0;
            $this->_request->item = array($item0);
        } else {
            $ccAuthService = new stdClass();
            $ccAuthService->run = "true";
            $this->_request->ccAuthService = $ccAuthService;

            $ccCaptureService = new stdClass();
            $ccCaptureService->run = "true";
            $this->_request->ccCaptureService = $ccCaptureService;

            $this->addBillingAddress($payment->getOrder()->getBillingAddress(), $payment->getOrder()->getCustomerEmail());
            $this->addShippingAddress($payment->getOrder()->getShippingAddress());
            $this->addCcInfo($payment);

            $purchaseTotals = new stdClass();
            $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
            $purchaseTotals->grandTotalAmount = $amount;
            $this->_request->purchaseTotals = $purchaseTotals;
        }
        try {
            $result = $soapClient->runTransaction($this->_request);
            if ($result->reasonCode==self::RESPONSE_CODE_SUCCESS) {
                /*
                for multiple capture we need to use the latest capture transaction id
                */
                $payment->setLastTransId($result->requestID)
                    ->setLastCybersourceToken($result->requestToken)
                    ->setCcTransId($result->requestID)
                    ->setTransactionId($result->requestID)
                    ->setIsTransactionClosed(0)
                    ->setCybersourceToken($result->requestToken)
                ;
            } else {
                 $error = Mage::helper('cybersource')->__('There is an error in processing the payment. ' . $this->_errors[$result->reasonCode] . ' Please try again or contact us.');
            }
        } catch (Exception $e) {
           Mage::throwException(
                Mage::helper('cybersource')->__('Gateway request error: %s', $e->getMessage())
            );
        }
        if ($error !== false) {
            Mage::throwException($error);
        }
        return $this;
    }

   /**
     * To assign transaction id and token after capturing payment
     *
     * @param Mage_Sale_Model_Order_Invoice $invoice
     * @param Mage_Sale_Model_Order_Payment $payment
     * @return Mage_Cybersource_Model_Soap
     */
    public function processInvoice($invoice, $payment)
    {
        parent::processInvoice($invoice, $payment);
        $invoice->setTransactionId($payment->getLastTransId());
        $invoice->setCybersourceToken($payment->getLastCybersourceToken());
        return $this;
    }

   /**
     * To assign transaction id and token before voiding the transaction
     *
     * @param Mage_Sale_Model_Order_Invoice $invoice
     * @param Mage_Sale_Order_Payment $payment
     * @return Mage_Cybersource_Model_Soap
     */
    public function processBeforeVoid($invoice, $payment)
    {
        parent::processBeforeVoid($invoice, $payment);
        $payment->setVoidTransactionId($invoice->getTransactionId());
        $payment->setVoidCybersourceToken($invoice->getCybersourceToken());
        return $this;
    }

   /**
     * Void the payment transaction
     *
     * @param Mage_Sale_Model_Order_Payment $payment
     * @return Mage_Cybersource_Model_Soap
     */
    public function void(Varien_Object $payment)
    {
        $error = false;
        if ($payment->getParentTransactionId() && $payment->getCybersourceToken()) {
            $soapClient = $this->getSoapApi();
            $this->iniRequest();
            $ccAuthReversalService = new stdClass();
            $ccAuthReversalService->run = "true";
            $ccAuthReversalService->authRequestID = $payment->getParentTransactionId();
            $ccAuthReversalService->authRequestToken = $payment->getCybersourceToken();
            $this->_request->ccAuthReversalService = $ccAuthReversalService;

            $purchaseTotals = new stdClass();
            $purchaseTotals->currency = $payment->getOrder()->getBaseCurrencyCode();
            $purchaseTotals->grandTotalAmount = $payment->getBaseAmountAuthorized();
            $this->_request->purchaseTotals = $purchaseTotals;

            try {
                $result = $soapClient->runTransaction($this->_request);
                if ($result->reasonCode==self::RESPONSE_CODE_SUCCESS) {
                    $payment->setTransactionId($result->requestID)
                        ->setCybersourceToken($result->requestToken)
                        ->setIsTransactionClosed(1);
                } else {
                     $error = Mage::helper('cybersource')->__('There is an error in processing the payment. ' . $this->_errors[$result->reasonCode] . ' Please try again or contact us.');
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
        return $this;
    }

   /**
     * To assign correct transaction id and token before refund
     *
     * @param Mage_Sale_Model_Order_Invoice $invoice
     * @param Mage_Sale_Model_Order_Payment $payment
     * @return Mage_Cybersource_Model_Soap
     */
    public function processBeforeRefund($invoice, $payment)
    {
        parent::processBeforeRefund($invoice, $payment);
        $payment->setRefundTransactionId($invoice->getTransactionId());
        $payment->setRefundCybersourceToken($invoice->getCybersourceToken());
        return $this;
    }

   /**
     * Refund the payment transaction
     *
     * @param Mage_Sale_Model_Order_Payment $payment
     * @param flaot $amount
     * @return Mage_Cybersource_Model_Soap
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $error = false;
        if ($payment->getParentTransactionId() && $payment->getRefundCybersourceToken() && $amount>0) {
            $soapClient = $this->getSoapApi();
            $this->iniRequest();
            $ccCreditService = new stdClass();
            $ccCreditService->run = "true";
            $ccCreditService->captureRequestToken = $payment->getCybersourceToken();
            $ccCreditService->captureRequestID = $payment->getParentTransactionId();
            $this->_request->ccCreditService = $ccCreditService;

            $purchaseTotals = new stdClass();
            $purchaseTotals->grandTotalAmount = $amount;
            $this->_request->purchaseTotals = $purchaseTotals;

            try {
                $result = $soapClient->runTransaction($this->_request);
                if ($result->reasonCode==self::RESPONSE_CODE_SUCCESS) {
                    $payment->setTransactionId($result->requestID)
                        ->setIsTransactionClosed(1)
                        ->setLastCybersourceToken($result->requestToken)
                        ;
                } else {
                     $error = Mage::helper('cybersource')->__('There is an error in processing the payment. ' . $this->_errors[$result->reasonCode] . ' Please try again or contact us.');
                }
            } catch (Exception $e) {
               Mage::throwException(
                    Mage::helper('cybersource')->__('Gateway request error: %s', $e->getMessage())
                );
            }
        } else {
            $error = Mage::helper('cybersource')->__('Error in refunding the payment.');
        }
        if ($error !== false) {
            Mage::throwException($error);
        }
        return $this;
    }


   /**
     * To assign correct transaction id and token after refund
     *
     * @param Mage_Sale_Model_Order_Creditmemo $creditmemo
     * @param Mage_Sale_Model_Order_Payment $payment
     * @return Mage_Cybersource_Model_Soap
     */
    public function processCreditmemo($creditmemo, $payment)
    {
        parent::processCreditmemo($creditmemo, $payment);
        $creditmemo->setTransactionId($payment->getLastTransId());
        $creditmemo->setCybersourceToken($payment->getLastCybersourceToken());
        return $this;
    }
}
