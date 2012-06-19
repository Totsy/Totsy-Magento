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

class Harapartners_Promotionfactory_Model_Observer {

    public function reserveVirtualProductCouponInCart(Varien_Event_Observer $observer) {
        $quoteItem = $observer->getEvent()->getItem();
        $product = $quoteItem->getProduct();

        if(!$product || !$product->getId()){
            return;
        }

        if(!$product->isVirtual()){
            return;
        }

        //Return when there is already a coupon code associated
        $reservationCodeOption = $quoteItem->getOptionByCode('reservation_code');
        if($reservationCodeOption instanceof Mage_Sales_Model_Quote_Item_Option
            && $reservationCodeOption->getId()){
            return;
        }

        $vpc = Mage::getModel('promotionfactory/virtualproductcoupon')
        ->loadOneByProductId($product->getId(), Harapartners_Promotionfactory_Model_Virtualproductcoupon::COUPON_STATUS_AVAILABLE );

        //'null' when current virtual product does not have any coupon code associated with it
        if($vpc === null){
            return;
        }

        if($vpc instanceof Harapartners_Promotionfactory_Model_Virtualproductcoupon){
            if($vpc->getId()){
                $newOption = Mage::getModel('sales/quote_item_option');
                $newOption->setData(
                    array('code' => 'reservation_code', 'value' => $vpc->getCode())
                );
                $newOption->setProduct($product);
                $newOption->setItem($quoteItem);
                $quoteItem->addOption($newOption);
                $vpc->setData('status', Harapartners_Promotionfactory_Model_Virtualproductcoupon::COUPON_STATUS_RESERVED)
                ->save();
            }else{
                Mage::throwException(sprintf("'%s' does not have any coupon code available.", $product->getName()));
            }
        }
    }

    public function releaseVirtualProductCouponInCart(Varien_Event_Observer $observer) {
        $quoteItem = $observer->getEvent()->getItem();
        $reservationCodeOption = $quoteItem->getOptionByCode('reservation_code');
        if($reservationCodeOption instanceof Mage_Sales_Model_Quote_Item_Option
            && $reservationCodeOption->getId()){
            $vpc = Mage::getModel('promotionfactory/virtualproductcoupon')->loadByCode($reservationCodeOption->getValue());
            if($vpc instanceof Harapartners_Promotionfactory_Model_Virtualproductcoupon
                && $vpc->getId()
                && $vpc->getStatus() == Harapartners_Promotionfactory_Model_Virtualproductcoupon::COUPON_STATUS_RESERVED){
                $vpc->setStatus(Harapartners_Promotionfactory_Model_Virtualproductcoupon::COUPON_STATUS_AVAILABLE)
                ->save();
            }
        }
    }

    public function purchaseVirtualProductCouponInOrder(Varien_Event_Observer $observer) {
        $orderItem = $observer->getEvent()->getItem();

        $reservationCodeOption = Mage::getModel('sales/quote_item_option')->getCollection()
        ->addFieldToFilter('item_id', $orderItem->getQuoteItemId())
        ->addFieldToFilter('code', 'reservation_code')
        ->getFirstItem();

        if($reservationCodeOption instanceof Mage_Sales_Model_Quote_Item_Option
            && $reservationCodeOption->getId()){
            $productOptions = unserialize($orderItem->getData('product_options'));
            if(!isset($productOptions['options'])){
                $productOptions['options'] = array();
            }
            $codeExist = false;
            foreach($productOptions['options'] as &$optionDataArray){
                if(isset($optionDataArray['label']) && $optionDataArray['label'] == 'Reservation Code'){
                    $codeExist = true;
                    break;
                }
            }
            if(!$codeExist){
                $productOptions['options'][] = array(
                    'label' => 'Reservation Code',
                    'value' => $reservationCodeOption->getValue()
                );
            }
            $orderItem->setData('product_options', serialize($productOptions));

            $vpc = Mage::getModel('promotionfactory/virtualproductcoupon')->loadByCode($reservationCodeOption->getValue());
            if($vpc->getId()){
                $vpc->setData('status', Harapartners_Promotionfactory_Model_Virtualproductcoupon::COUPON_STATUS_PURCHASED)
                ->save();
            }else{
                Mage::getSingleton('checkout/session')->addError('There was an error while placing the order with your reserved coupon code.');
            }

        }
    }

    public function saleOrderPlaceAfter(Varien_Event_Observer $observer) {
        
        $order = $observer->getEvent()->getOrder();
        $email = $order->getCustomer()->getEmail();
                
        if(!$order || !$order->getId()) {
            return;
        }

        foreach ( $order->getAllItems() as $orderItem ) {

            if ($orderItem->getProductType() == 'virtual') {

                $optionByCode = $orderItem->getProductOptionByCode();
                //getting the virtual product code
                $virtualProductCode = $optionByCode['options'][0]['value'];
                
                $shortDescription = $orderItem->getShortDescription();

                //picking the right template by the id set in the admin (transactional emails section)
                $templateId =  Mage::getModel('core/email_template')->loadByCode('_trans_Virtual_Product_Redemption')->getId();

                $name = "Evan";
                $sender = "evanubiera@gmail.com";
                $store = Mage::app()->getStore();

                $mailSubject = "Here is your Totsy coupon redemption code";

                //attempting to send the email
                try {
                    Mage::getModel('core/email_template')
                    ->sendTransactional($templateId, "support", $email,NULL, array("virtual_product_code"=>$virtualProductCode, "order"=>$order, "store"=>$store, "short_description" => $shortDescription));
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }
                
        $couponCode = $order->getQuote()->getCouponCode();
        if(!$couponCode){
            return;
        }

        $groupCoupon = Mage::getModel('promotionfactory/groupcoupon')->loadByPseudoCode($couponCode);
        if(!!$groupCoupon && !!$groupCoupon->getId()){
            $groupCoupon->setData('used_count', $groupCoupon->getUsedCount() + 1);
            $groupCoupon->save();
        }

        $customer = $order->getCustomer();
        if(!!$customer && !!$customer->getId()){
            $emailCoupon = Mage::getModel('promotionfactory/emailcoupon')->loadByEmailCouponWithEmail($couponCode, $customer->getEmail());
            if(!!$emailCoupon && !!$emailCoupon->getId()){
                $emailCoupon->setData('used_count', $emailCoupon->getUsedCount() + 1);
                $emailCoupon->save();
            }
        }
    }

}