<?php 
class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Payment extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
    public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array())
    {
        $customerId = $order->getCustomerId();
        $billing = $order->getBillingAddress();
        $payment = new Varien_Object($data);
        $profile = Mage::getModel('paymentfactory/tokenize')->createProfile($payment, $billing, $customerId, $billing->getId());
        $infos = $profile->getData();
    }

    public function replaceProfile() {
    }
}