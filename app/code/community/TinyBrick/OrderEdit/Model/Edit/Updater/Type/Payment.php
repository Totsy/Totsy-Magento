<?php 

class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Payment extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
	public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array(), $billing)
	{
        $billing = $order->getBillingAddress();
        $tokenize = Mage::getModel('paymentfactory/tokenize');
        $profile = $tokenize->createProfile($data['payment'], $data, $customerId, $billing->getId(), $order->getCustomerId());
        $infos = $profile->getData();
    }
}