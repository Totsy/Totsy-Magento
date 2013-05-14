<?php
/**
 * @category    Totsy
 * @package     Totsy_CreditCard_Model_PaymentLogic
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

require_once ('Litle/LitleSDK/LitleOnline.php');

class Totsy_CreditCard_Model_PaymentLogic extends Litle_CreditCard_Model_PaymentLogic
{
    public function getTokenInfo($payment)
    {
        $vaultIndex = $this->getInfoInstance()->getAdditionalInformation('cc_vaulted');
        $vaultCard = Mage::getModel('palorus/vault')->load($vaultIndex);

        $expDate = $vaultCard->getExpirationMonth() . substr($vaultCard->getExpirationYear(), -2);
        if(strlen($expDate) < 4) {
            $expDate = '0' . $expDate;
        }

        $retArray = array();
        $retArray['expDate'] = $expDate;
        $retArray['litleToken'] = $vaultCard->getToken();
        $retArray['cardValidationNum'] = $payment->getCcCid();

        $payment->setCcLast4($vaultCard->getLast4());
        $payment->setCcType($vaultCard->getType());

        return $retArray;
    }
    
    /**
     * Update Vaulted card information.
     *
     * @param Varien_Object $payment
     * @param DOMDocument $litleResponse
     * @param String $customerAddressId
     */
    protected function _saveToken(Varien_Object $payment, DOMDocument $litleResponse, $customerAddressId = null)
    {
        if (!is_null($this->getUpdater($litleResponse, 'tokenResponse')) &&
            !is_null($this->getUpdater($litleResponse, 'tokenResponse', 'litleToken'))) {

            $vault = Mage::getModel('palorus/vault')->setTokenFromPayment(
                    $payment,
                    $this->getUpdater($litleResponse, 'tokenResponse', 'litleToken'),
                    $this->getUpdater($litleResponse, 'tokenResponse', 'bin'));
            if($customerAddressId) {
                $vault->setData('address_id', $customerAddressId)
                    ->save();
            }
            $this->getInfoInstance()->setAdditionalInformation('vault_id', $vault->getId());
        }
    }

    public function writeFailedTransactionToDatabase($customerId, $orderId, $message, $xmlDocument) {
        $orderNumber = 0;
        if($orderId === null) {
            $orderId = 0;
        }
        else {
            $order = Mage::getModel("sales/order")->load($orderId);
            $orderNumber = $order->getData("increment_id");
        }
        if($customerId === null) {
            $customerId = 0;
        }
        $db = Mage::getSingleton('core/resource')->getConnection('core/write');

        $fullXml = $xmlDocument->saveXML();
        if (!$db)
        {
            Mage::log("Failed to write failed transaction to database.  Transaction details: " . $fullXml, null, "litle_failed_transactions.log");
        }
        else {
            $litleTxnId = XMLParser::getNode($xmlDocument, 'litleTxnId');
            $sql = "insert into litle_failed_transactions (customer_id, order_id, message, full_xml, litle_txn_id, active, transaction_timestamp, order_num) values (?, ?, ?, ?, ?, true, now(), ?)";

            try {
                $result = $db->query($sql,array($customerId,$orderId,$message,$fullXml,$litleTxnId,$orderNumber));

            } catch(Exception $e) {
                Mage::log("Insert failed with error message: " . $e->getMessage, null, "litle.log");
                Mage::log("Query executed: " . $sql, null, "litle.log");
            }
        }
    }

    /**
     * this method is called if we are just authorising a transaction
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        Mage::log('totsy_authorize');
        $order = $payment->getOrder();
        $orderId = $order->getIncrementId();

        if($order->getStatus() != 'processing') {
            $amount = 1.00;
        }

        $amountToPass = Mage::helper('creditcard')->formatAmount($amount, true);

        if (! empty($order)) {
            $info = $this->getInfoInstance();
            if (!$info->getAdditionalInformation('orderSource')) {
                $info->setAdditionalInformation('orderSource', 'ecommerce');
            }

            $hash = array(
                    'orderId' => $orderId,
                    'amount' => $amountToPass,
                    'orderSource' => $info->getAdditionalInformation('orderSource'),
                    'billToAddress' => $this->getBillToAddress($payment),
                    'shipToAddress' => $this->getAddressInfo($payment),
                    'cardholderAuthentication' => $this->getFraudCheck($payment),
                    'enhancedData' => $this->getEnhancedData($payment)
            );

            $payment_hash = $this->creditCardOrPaypageOrToken($payment);
            $hash_temp = array_merge($hash, $payment_hash);
            $merchantData = $this->merchantData($payment);
            $hash_in = array_merge($hash_temp, $merchantData);
            $litleRequest = new LitleOnlineRequest();
            $litleResponse = $litleRequest->authorizationRequest($hash_in);
            $this->processResponse($payment, $litleResponse);
            Mage::helper('palorus')->saveCustomerInsight($payment, $litleResponse);
            if (!is_null($info->getAdditionalInformation('cc_should_save'))) {
                $customerAddressId = $this->saveCustomerAddress($payment);
                $this->_saveToken($payment, $litleResponse, $customerAddressId);
            }
        }
        return $this;
    }

    public function saveCustomerAddress($payment) {
        $addressCustomer = Mage::getModel('customer/address');
        $customerId = $payment->getOrder()->getCustomerId();
        $billingAddressDatas = $payment->getOrder()->getBillingAddress()->getData();
        unset($billingAddressDatas['entity_id']);
        $addressCustomer->setData($billingAddressDatas)
            ->setCustomerId($customerId)
            ->setIsDefaultBilling(false)
            ->setIsDefaultShipping(false)
            ->save();
        return $addressCustomer->getId();
    }

    /**
     * called if voiding a payment
     */
    public function void(Varien_Object $payment)
    {
        $this->isFromVT($payment, 'void');

        $order = $payment->getOrder();
        if (! empty($order)) {
            $hash = array(
                'litleTxnId' => $payment->getCcTransId()
            );
            $merchantData = $this->merchantData($payment);
            $hash_in = array_merge($hash, $merchantData);
            $litleRequest = new LitleOnlineRequest();

            if (Mage::helper('creditcard')->isStateOfOrderEqualTo($order,
                Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH)) {
                $litleResponse = $litleRequest->authReversalRequest($hash_in);
                //Skip Error Handling if we get a general decline on an initial auth
                $litleResponseCode = XMLParser::getNode($litleResponse, 'response');
                if ($litleResponseCode != '000' && $order->getStatus() == 'pending') {
                    return $this;
                }
            } else {
                $litleResponse = $litleRequest->voidRequest($hash_in);
            }
        }

        $this->processResponse($payment, $litleResponse);

        return $this;
    }
}