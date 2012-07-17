<?php 
class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Payment extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
    public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array())
    {
        try {
            $customerId = $order->getCustomerId();
            $billingId = $order->getData('billing_address_id');
            $billing = Mage::getModel('sales/order_address')->load($billingId);
            $data = $this->cleanPaymentData($data);
            $payment = new Varien_Object($data);
            #Check if there is already a cybersource profile if yes, dont create a new one
            $profile = Mage::getModel('paymentfactory/profile');
            $profile->loadByCcNumberWithId($payment->getData('cc_number').$customerId.$payment->getCcExpYear().$payment->getCcExpMonth());
            if($profile && $profile->getId()) {
                $paymentObject = Mage::getModel('sales/order_payment')->getCollection()
                    ->addAttributeToFilter('cybersource_subid',$profile->getData('subscription_id'))
                    ->getFirstItem();
                $payment = $paymentObject->getData();
                $payment['cybersource_subid'] = null;
                if(!$payment) {
                    return false;
                }
            }
            Mage::getModel('paymentfactory/tokenize')->createProfile($payment, $billing, $customerId, $billingId);
            $this->replacePaymentInformation($order, $payment);
        } catch(Exception $e){
            return "Error updating payment informations : ".$e->getMessage();
        }
        return false;
    }

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

    public function replacePaymentInformation($order, $payment) {
        $paymentOrder = $order->getPayment();
        $paymentOrder->setData('cc_exp_month', $payment->getData('cc_exp_month'));
        $paymentOrder->setData('cc_last4', substr($payment->getCcNumber(), -4));
        $paymentOrder->setData('cc_type', $payment->getData('cc_type'));
        $paymentOrder->setData('cc_exp_year', $payment->getData('cc_exp_year'));
        $paymentOrder->setData('cc_trans_id', $payment->getData('cc_trans_id'));
        $paymentOrder->setData('cybersource_token', $payment->getData('cybersource_token'));
        $paymentOrder->setData('cybersource_subid', $payment->getData('cybersource_subid'));
        $paymentOrder->save();
    }
}