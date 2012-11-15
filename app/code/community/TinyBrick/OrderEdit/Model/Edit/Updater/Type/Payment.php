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
            $addressUpdated = $data['addressUpdated'];
            //Setting datas linked with the order and payment
            $savingNewCreditCard = true;
            $customerId = $order->getCustomerId();
            $billingId = $order->getBillingAddressId();
            $billing = Mage::getModel('sales/order_address')->load($billingId);
            $customerAddressId = Mage::getModel('orderedit/edit_updater_type_billing')->getCustomerAddressFromBilling($billingId);
            if(!$customerAddressId) {
                return "Error updating payment informations : Address is not attached to the Customer";
            }
            $data = $this->cleanPaymentData($data);
            $payment = new Varien_Object($data);
            if($payment->getMethod() == 'free') {
                $this->replacePaymentInformation($order, $payment);
                $this->makeOrderReadyToBeProcessed($order);
                return false;
            }
            $payment->setData('cc_last4', substr($payment->getCcNumber(), -4));
            #Check if a cybersource profile already exist with those informations
            $profile = Mage::getModel('paymentfactory/profile');
            if($payment->getData('cybersource_subid')) {
                $profile->loadByEncryptedSubscriptionId($payment->getData('cybersource_subid'));
            } else if($payment->getData('cc_number')) {
                $profile->loadByCcNumberWithId($payment->getData('cc_number').$customerId.$payment->getCcExpYear().$payment->getCcExpMonth());
            }
            if($profile && $profile->getId()) {
                $savingNewCreditCard = false;
                $payment = Mage::getModel('sales/order_payment')->getCollection()
                    ->addAttributeToFilter('cybersource_subid',$profile->getData('subscription_id'))
                    ->getFirstItem();
                if((!$payment || !$payment->getId()) && !$addressUpdated) {
                    // Specific Case if payment informations has been deleted from the object sales_flat_order_payment
                    // Refill informations using the profile
                    $payment = new Varien_Object($data);
                    $payment->setData('cc_last4', substr($payment->getCcNumber(), -4))
                            ->setData('cybersource_subid',$profile->getData('subscription_id'));
                } else {
                    //If profile exist but the billing address has been updated, update the address id.
                    if($addressUpdated) {
                        $profile->setData('address_id', $customerAddressId)->save();
                    }
                }
            }
            if($savingNewCreditCard) {
                $billing->setData('email', $order->getCustomerEmail());
                Mage::getModel('paymentfactory/tokenize')->createProfile($payment, $billing, $customerId, $customerAddressId);
            }
            if(!$payment->getData('cc_last4')) {
               return "Error updating payment informations : Missing Fields";
            }
            $this->replacePaymentInformation($order, $payment);
            $this->makeOrderReadyToBeProcessed($order);
            $virtualProductCoupon = Mage::getModel('promotionfactory/virtualproductcoupon');
            $virtualProductCoupon->openVirtualProductCouponInOrder($order);
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
     * Update Payment Informations of the Order
     */
    public function replacePaymentInformation($order, $newPayment) {
        $paymentOrder = $order->getPayment();
        if($newPayment->getData('method')) {
            $paymentOrder->setData('method', $newPayment->getData('method'));
        }
        $paymentOrder->setData('cc_exp_month', $newPayment->getData('cc_exp_month'))
                     ->setData('cc_last4', $newPayment->getData('cc_last4'))
                     ->setData('cc_type', $newPayment->getData('cc_type'))
                     ->setData('cc_exp_year', $newPayment->getData('cc_exp_year'))
                     ->setData('cc_trans_id', $newPayment->getData('cc_trans_id'))
                     ->setData('cybersource_token', $newPayment->getData('cybersource_token'))
                     ->setData('cybersource_subid', $newPayment->getData('cybersource_subid'))
                     ->save();
    }

    /**
     * For ItemsQueue linked with the order, switch status to pending.
     */
    public function makeOrderReadyToBeProcessed($order) {
      if($order->getStatus() == Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED) {
            $order->setStatus('pending')
                ->save();
            $collection = Mage::getModel('fulfillmentfactory/itemqueue')->getCollection()->loadByOrderId($order->getId());
            foreach($collection as $itemqueue) {
                $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PENDING)
                          ->save();
            }
        }
    }
}