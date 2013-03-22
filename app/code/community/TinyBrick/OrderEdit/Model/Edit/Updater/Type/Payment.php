<?php 
/**
 * @category    TinyBrick
 * @package     TinyBrick_OrderEdit_Model_Edit_Updater_Type_Payment
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */
require_once Mage::getBaseDir('code') . '/community/Litle/LitleSDK/LitleOnline.php';

class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Payment extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @param array $data
     *      "method" => string
     *      "cc_type" => string
     *      "cc_number" => string
     *      "cc_exp_month" => string
     *      "cc_exp_year" => string
     *      "cc_cid" => string
     * @return bool|string|void
     */
    public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array())
    {
        try {
            if(array_key_exists('original',$data) && $data['original']) {
                return false;
            }
            $payment = new Varien_Object($data);
            if($payment->getData('cc_vaulted')) {
                $vault = Mage::getModel('palorus/vault')->load($payment->getData('cc_vaulted'));
                if($vault->getId()) {
                    $payment->setData('token', $vault->getData('token'))
                            ->setData('bin', $vault->getData('bin'))
                            ->setData('type', $vault->getData('type'))
                            ->setData('cc_type', $vault->getData('type'))
                            ->setData('cc_last4', $vault->getData('last4'))
                            ->setData('cc_exp_month', $vault->getData('expiration_month'))
                            ->setData('cc_exp_year', $vault->getData('expiration_year'));
                    $response = $this->authorizationWithToken($order, $payment);
                    if(!$response) {
                        return "Error updating payment : Please check the informations you have entered.";
                    }
                    return false;
                }
            }
            $billingId = $order->getBillingAddressId();
            $customerAddressId = Mage::getModel('orderedit/edit_updater_type_billing')->getCustomerAddressFromBilling($billingId);
            if(!$customerAddressId) {
                return "Error updating payment informations : Address is not attached to the Customer";
            }
            if($payment->getMethod() == 'free') {
                $this->replacePaymentInformation($order, $payment);
                return false;
            }
            $response = $this->authorizationWithCard($order, $payment);
            if(!$response) {
                return "Error updating payment : Please check the informations you have entered.";
            }
        } catch(Exception $e) {
            return "Error updating payment informations : ".$e->getMessage();
        }
        return false;
    }

    /**
     * Update Payment Informations of the Order
     */
    public function replacePaymentInformation($order, $newPayment) {
        $paymentOrder = $order->getPayment();
        if($newPayment->getData('method')) {
            $paymentOrder->setData('method', $newPayment->getData('method'));
        }
        if($newPayment->getData('litle_vault_id')) {
            $paymentOrder->setData('litle_vault_id', $newPayment->getData('litle_vault_id'));
        }
        $paymentOrder->setData('cc_exp_month', $newPayment->getData('cc_exp_month'))
                     ->setData('cc_last4', $newPayment->getData('cc_last4'))
                     ->setData('cc_type', $newPayment->getData('cc_type'))
                     ->setData('cc_exp_year', $newPayment->getData('cc_exp_year'))
                     ->setData('last_trans_id', $newPayment->getData('last_trans_id'))
                     ->setData('cc_trans_id', $newPayment->getData('cc_trans_id'))
                     ->save();
    }

    /**
     * Create Authorization through Litle
     */
    public function authorizationWithCard($order, $payment) {
        $billingAddress = $order->getBillingAddress();
        $street = $billingAddress->getStreet();
        $expDate = $payment->getCcExpMonth() . substr($payment->getCcExpYear(), -2);
        if(strlen($expDate) < 4) {
            $expDate = '0' . $expDate;
        }
        #Authorization
        $auth_info = array(
            'orderId' => $order->getId(),
            'amount' => (int) ($order->getGrandTotal() * 100),
            'id'=> '456',
            'orderSource'=>'ecommerce',
            'billToAddress'=>array(
                'name' => $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(),
                'addressLine1' => (is_array($street))
                    ? $street[0] . ' ' . $street[1]
                    : $street,
                'city' => $billingAddress->getCity(),
                'state' => $billingAddress->getRegion(),
                'zip' => $billingAddress->getPostcode(),
                'country' => 'US'),
            'card'=>array(
                'number' =>$payment->getCcNumber(),
                'expDate' => $expDate,
                'cardValidationNum' => $payment->getCcCid(),
                'type' => $payment->getCcType())
        );

        $initialize = new LitleOnlineRequest();
        $authResponse = $initialize->authorizationRequest($auth_info);
        $transactionId =  XmlParser::getNode($authResponse,'litleTxnId');

        if($transactionId) {
            $payment->setData('last_trans_id', $transactionId)
                    ->setData('cc_trans_id', $transactionId);
        } else {
            return false;
        }
        if($payment->getCcNumber()) {
            $payment->setData('cc_last4', substr($payment->getCcNumber(), -4));
        }

        $this->replacePaymentInformation($order, $payment);

        //Create Vault Profile if option selected
        if($payment->getData('cc_should_save') == 'on') {
            $paymentObject = $order->getPayment();
            $paymentObject->setCcNumber($payment->getCcNumber());
            $vault = Mage::getModel('palorus/vault')->setTokenFromPayment(
                $paymentObject,
                Mage::getModel('Litle_CreditCard_Model_PaymentLogic')->getUpdater($authResponse, 'tokenResponse', 'litleToken'),
                Mage::getModel('Litle_CreditCard_Model_PaymentLogic')->getUpdater($authResponse, 'tokenResponse', 'bin'));
            $vault->setData('address_id', $billingAddress->getId())
                  ->save();
        }
        return true;
    }

    /**
     * Create Authorization through Litle
     */
    public function authorizationWithToken($order, $payment) {
        #Authorization with Token
        $auth_info = array(
            'orderId' => $order->getId(),
            'amount' => (int) ($order->getGrandTotal() * 100),
            'id'=> '456',
            'orderSource'=>'ecommerce',
            'token' => array(
                'litleToken' => $payment->getData('token'),
                'bin' => $payment->getData('bin'),
                'type' => $payment->getData('type')
            )
        );

        $initialize = new LitleOnlineRequest();
        $authResponse = $initialize->authorizationRequest($auth_info);
        $transactionId =  XmlParser::getNode($authResponse,'litleTxnId');

        if($transactionId) {
            $payment->setData('last_trans_id', $transactionId)
                    ->setData('cc_trans_id', $transactionId);
            $this->replacePaymentInformation($order, $payment);
            return true;
        } else {
            return false;
        }
    }
}