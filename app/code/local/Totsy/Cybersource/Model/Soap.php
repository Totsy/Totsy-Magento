<?php
/**
 * @category    Totsy
 * @package     Totsy_Cybersource_Model
 * @author      troyer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Cybersource_Model_Soap extends Mage_Cybersource_Model_Soap
{

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
     * Credit customer profile
     *
     * @param Mage_Sale_Model_Order_Payment $payment
     * @param flaot $amount
     * @return Mage_Cybersource_Model_Soap
     */
    public function credit($subscription_id, $amount)
    {
        $error = false;
        if ($subscription_id && $amount>0) {
            $soapClient = $this->getSoapApi();
            $this->iniRequest();
            $ccCreditService = new stdClass();
            $ccCreditService->run = "true";
            $this->_request->ccCreditService = $ccCreditService;

            $subscription_info = new stdClass();
            $subscription_info->subscriptionID = $subscription_id;
            $this->_request->recurringSubscriptionInfo = $subscription_info;

            $purchaseTotals = new stdClass();
            $purchaseTotals->grandTotalAmount = $amount;
            $this->_request->purchaseTotals = $purchaseTotals;

            try {
                $result = $soapClient->runTransaction($this->_request);
                if ($result->reasonCode==self::RESPONSE_CODE_SUCCESS) {

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
}
