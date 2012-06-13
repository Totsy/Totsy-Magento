<?php


class Harapartners_Inviteeorder_Model_Observer
{
  //Hara Song
    public function inviteeFirstOrder(Varien_Event_Observer $observer)
    {
    	if (Mage::helper('Core')->isModuleEnabled('Enterprise_Invitation')) {
            $invoice = $observer->getEvent()->getInvoice();
            /* @var $invoice Mage_Sales_Model_Order_Invoice */
            $order = $invoice->getOrder();
            /* @var $order Mage_Sales_Model_Order */
            if ($order->getBaseTotalDue() > 0) {
                return $this;
            }
            $invitation = Mage::getModel('enterprise_invitation/invitation')
                ->load($order->getCustomerId(), 'referral_id');
            if (!$invitation->getId() || !$invitation->getCustomerId()) {
                return $this;
            }
            $ordered = $invitation->getData('order_increment_id');
            //$status = $invitation->getData('status');
            if(!$ordered){
            	$invitation->setData('order_increment_id', $order->getIncrementId());
            	$invitation->save();
            }
        }

        return $this;
    }
}