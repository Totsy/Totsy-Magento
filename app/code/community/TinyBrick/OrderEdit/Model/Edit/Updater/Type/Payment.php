<?php 
/**
 * @category    TinyBrick
 * @package     TinyBrick_OrderEdit_Model_Edit_Updater_Type_Payment
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Payment extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
    public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array())
    {
        try {
            $savingNewCreditCard = true;
            $customerId = $order->getCustomerId();
            $billingId = $order->getData('billing_address_id');
            $billing = Mage::getModel('sales/order_address')->load($billingId);
            $data = $this->cleanPaymentData($data);
            $payment = new Varien_Object($data);
            $payment->setData('cc_last4', substr($payment->getCcNumber(), -4));
            #If credit Card saved retrieve payment profile
            if($payment->getData('cybersource_subid')) {
                /**$decryptedCybersourceId = Mage::getModel('core/encryption')->decrypt(base64_decode($payment->getData('cybersource_subid')));
                $payment = Mage::getModel('sales/order_payment')->getCollection()
                    ->addAttributeToFilter('cybersource_subid',$decryptedCybersourceId)
                    ->getFirstItem();
                if(!$payment) {
                    return false;
                } else {
                	$savingNewCreditCard = false;
                }**/
            } else {
                #Check if there is already a cybersource profile if yes, dont create a new one
                $profile = Mage::getModel('paymentfactory/profile');
                $profile->loadByCcNumberWithId($payment->getData('cc_number').$customerId.$payment->getCcExpYear().$payment->getCcExpMonth());
                if($profile && $profile->getId()) {
                    $payment = Mage::getModel('sales/order_payment')->getCollection()
                        ->addAttributeToFilter('cybersource_subid',$profile->getData('subscription_id'))
                        ->getFirstItem();
                    if(!$payment) {
                        return false;
                    } else {
                    	$savingNewCreditCard = false;
                    }
                }
            }
            if($savingNewCreditCard) {
                $billing->setData('email', $order->getCustomerEmail());
                Mage::getModel('paymentfactory/tokenize')->createProfile($payment, $billing, $customerId, $billingId);
            }
            $this->replacePaymentInformation($order, $payment);
            if($order->getStatus() == Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED) {
                $order->setStatus('pending')
                    ->save();
                $collection = Mage::getModel('fulfillmentfactory/itemqueue')->getCollection()->loadByOrderId($order->getId());
                foreach($collection as $itemqueue) {
                    $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PENDING);
                    $itemqueue->save();
                }
            }
        } catch(Exception $e){
            return "Error updating payment informations : ".$e->getMessage();
        }
        return false;
    }

    /**
     * Clean Payment datas got from the post to be able to process them
     */
    public function cleanPaymentData($datas) {
        $cleanDatas = null;
        foreach($datas as  $key => $data) {
            $key = str_replace('[','',$key);
            $key = str_replace(']','',$key);
            $key = str_replace('payment','',$key);
            $cleanDatas[$key] = $data;
        }
        return $cleanDatas;
    }

    /**
     * Update Payment Informations from the Order
     */
    public function replacePaymentInformation($order, $payment) {
        $paymentOrder = $order->getPayment();
        $paymentOrder->setData('cc_exp_month', $payment->getData('cc_exp_month'));
        $paymentOrder->setData('cc_last4', $payment->getData('cc_last4'));
        $paymentOrder->setData('cc_type', $payment->getData('cc_type'));
        $paymentOrder->setData('cc_exp_year', $payment->getData('cc_exp_year'));
        $paymentOrder->setData('cc_trans_id', $payment->getData('cc_trans_id'));
        $paymentOrder->setData('cybersource_token', $payment->getData('cybersource_token'));
        $paymentOrder->setData('cybersource_subid', $payment->getData('cybersource_subid'));
        $paymentOrder->save();
    }
}