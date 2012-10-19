<?php
class Crown_Club_Model_Paypal_Ipn extends Mage_Paypal_Model_Ipn {
	
	protected function _registerPaymentCapture() {
		parent::_registerPaymentCapture();
		Mage::log('Start',null,'test.log',true);
		$helper = Mage::helper('crownclub');
		if (!$helper->moduleSetupComplete()) return;
		Mage::log('Passed module check',null,'test.log',true);
		/* @var $order Mage_Sales_Model_Order */
		$order = $this->_order;
		Mage::log('Loaded Order',null,'test.log',true);
		if ($order->getCustomerIsGuest()) return;
		$customerId = $order->getCustomerId();
		Mage::log('Customer not guest',null,'test.log',true);
		$clubMemberNow = false;
		$orderItems = $order->getItemsCollection();
		Mage::log('Loaded order items',null,'test.log',true);
		foreach ($orderItems as $orderItem) {
			/* @var $orderItem Mage_Sales_Model_Order_Item */
			Mage::log('Looping through order items',null,'test.log',true);
			if ($orderItem->hasData('is_club_subscription')) {
				Mage::log('Club item found',null,'test.log',true);
				$clubMemberNow = true;
			}
		}
		Mage::log('Checking if club is to be active',null,'test.log',true);
		if ($clubMemberNow) {
			Mage::log('Customer to be converted',null,'test.log',true);
			$club = Mage::getModel('crownclub/club');
			$customer = Mage::getModel('customer/customer')->load($customerId);
			$club->addClubMember($customer);
			Mage::log('Customer converted',null,'test.log',true);
		}
	}
}