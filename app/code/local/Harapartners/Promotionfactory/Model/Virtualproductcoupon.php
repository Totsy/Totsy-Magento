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

class Harapartners_Promotionfactory_Model_Virtualproductcoupon extends Mage_Core_Model_Abstract {
	
	const COUPON_STATUS_AVAILABLE = 0;
	const COUPON_STATUS_RESERVED = 1;
	const COUPON_STATUS_PURCHASED = 2;
	
	protected function _construct(){
        $this->_init('promotionfactory/virtualproductcoupon');
    }
    
	protected function _beforeSave(){
        $datetime = date('Y-m-d H:i:s');
        if(!$this->getId()){
            $this->setData('created_at', $datetime);
        }
        $this->setData('updated_at', $datetime);
        if(!$this->getStoreId()){
            $this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
        }
        parent::_beforeSave();
    }

    public function cancelVirtualProductCouponInOrder($order) {
        foreach($order->getAllItems() as $orderItem){
            if($orderItem->getProductType() != 'virtual'){
                continue;
            }
            $productOptions = unserialize($orderItem->getData('product_options'));
            if(isset($productOptions['options'])){
                foreach($productOptions['options'] as $optionDataArray){
                    if(isset($optionDataArray['label'])
                        && $optionDataArray['value']){
                        $textDetail = "\n You will receive an additional email with details on this code";
                        $codeCoupon = str_replace($textDetail,'', $optionDataArray['value']);
                        $vpc = Mage::getModel('promotionfactory/virtualproductcoupon')->load($codeCoupon, 'code');
                        if($vpc->getId()){
                            $vpc->setData('status', Harapartners_Promotionfactory_Model_Virtualproductcoupon::COUPON_STATUS_RESERVED)
                            ->save();
                        }
                    }
                }
            }
        }
        return;
    }

    public function openVirtualProductCouponInOrder($order) {
        foreach($order->getAllItems() as $orderItem){
            if($orderItem->getProductType() != 'virtual'){
                continue;
            }
            $productOptions = unserialize($orderItem->getData('product_options'));
            if(isset($productOptions['options'])){
                foreach($productOptions['options'] as $optionDataArray){
                    if(isset($optionDataArray['label'])
                        && $optionDataArray['value']){
                        $textDetail = "\n You will receive an additional email with details on this code";
                        $codeCoupon = str_replace($textDetail,'', $optionDataArray['value']);
                        $vpc = Mage::getModel('promotionfactory/virtualproductcoupon')->load($codeCoupon, 'code');
                        if($vpc->getId()){
                            $vpc->setData('status', Harapartners_Promotionfactory_Model_Virtualproductcoupon::COUPON_STATUS_PURCHASED)
                                ->setData('order_id', $order->getId())
                                ->setData('order_increment_id', $order->getIncrementId())
                            ->save();
                        }
                    }
                }
            }
        }
        return;
    }
    
    

}