<?php 
/**
 * @category    TinyBrick
 * @package     TinyBrick_OrderEdit_Model_Edit_Updater_Type_Payment
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Payment extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
    /**
     * @param Mage_Sales_Model_Order $order
     * @param array $data
     *      "method" => string
     *      "cybersource_subid" => string
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
            $payment = new Varien_Object($data);
            $profile = Mage::getModel('paymentfactory/profile');
            //Setting datas linked with the order and payment
            $savingNewCreditCard = true;
            $customerId = $order->getCustomerId();
            $billingId = $order->getBillingAddressId();
            $billing = Mage::getModel('sales/order_address')->load($billingId);
            $customerAddressId = Mage::getModel('orderedit/edit_updater_type_billing')->getCustomerAddressFromBilling($billingId);
            if(!$customerAddressId) {
                return "Error updating payment informations : Address is not attached to the Customer";
            }
            if($payment->getMethod() == 'free') {
                $this->replacePaymentInformation($order, $payment);
                return false;
            }
            $payment->setData('cc_last4', substr($payment->getCcNumber(), -4));
            #Check if a cybersource profile already exist with those informations
            if($payment->getData('cybersource_subid')) {
                $profile->loadByEncryptedSubscriptionId($payment->getData('cybersource_subid'));
            } else if($payment->getData('cc_number')) {
                $profile->loadByCcNumberWithId($payment->getData('cc_number').$customerId.$payment->getCcExpYear().$payment->getCcExpMonth());
            } else {
                return "Error updating payment informations : Missing Fields";
            }
            if($profile && $profile->getId()) {
                $savingNewCreditCard = false;
                $payment = Mage::getModel('sales/order_payment')->getCollection()
                    ->addAttributeToFilter('cybersource_subid',$profile->getData('subscription_id'))
                    ->getFirstItem();
                if(!$payment || !$payment->getId()) {
                    // Specific Case if payment informations has been deleted from the object sales_flat_order_payment
                    // Refill informations using the profile
                    $payment = new Varien_Object($data);
                    $payment->setData('cc_last4', $profile->getData('last4no'))
                            ->setData('cc_exp_year', $profile->getData('expire_year'))
                            ->setData('cc_exp_month', $profile->getData('expire_month'))
                            ->setData('cc_type', $profile->getData('card_type'))
                            ->setData('cybersource_subid',$profile->getData('subscription_id'));
                }
            }
            if($savingNewCreditCard) {
                $billing->setData('email', $order->getCustomerEmail());
                Mage::getModel('paymentfactory/tokenize')->createProfile($payment, $billing, $customerId, $customerAddressId);
            }
            $this->replacePaymentInformation($order, $payment);
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
        $paymentOrder->setData('cc_exp_month', $newPayment->getData('cc_exp_month'))
                     ->setData('cc_last4', $newPayment->getData('cc_last4'))
                     ->setData('cc_type', $newPayment->getData('cc_type'))
                     ->setData('cc_exp_year', $newPayment->getData('cc_exp_year'))
                     ->setData('cc_trans_id', $newPayment->getData('cc_trans_id'))
                     ->setData('cybersource_token', $newPayment->getData('cybersource_token'))
                     ->setData('cybersource_subid', $newPayment->getData('cybersource_subid'))
                     ->save();
    }
}