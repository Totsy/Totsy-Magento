<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Service_Model_Rewrite_Sales_Convert_Quote extends Mage_Sales_Model_Convert_Quote {
    
    public function paymentToOrderPayment(Mage_Sales_Model_Quote_Payment $payment){
        $orderPayment = parent::paymentToOrderPayment($payment);
        if(!!$payment->getData('cybersource_subid')){
            $orderPayment->setData('cybersource_subid', $payment->getData('cybersource_subid'));
            $orderPayment->getMethodInstance()
                    ->setData('cybersource_subid', $payment->getMethodInstance()->getData('cybersource_subid'));
        }
        return $orderPayment;
    }
    
    
    //Important logic for order split and DOTCOM fulfillment, must link new order item with original quote item!
    /*
    public function itemToOrderItem(Mage_Sales_Model_Quote_Item_Abstract $item){
        $orderItem = parent::itemToOrderItem($item);
        if(!!$item->getOriginalQuoteItemId()){
            $orderItem->setOriginalQuoteItemId($item->getOriginalQuoteItemId());
        }else{
            $orderItem->setOriginalQuoteItemId($item->getItemId());
        }
        return $orderItem;
    }
    */
    
}