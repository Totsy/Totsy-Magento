<?php
/**
 * TinyBrick Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the TinyBrick Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.delorumcommerce.com/license/commercial-extension
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@tinybrick.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future. 
 *
 * @category   TinyBrick
 * @package    TinyBrick_OrderEdit
 * @copyright  Copyright (c) 2010 TinyBrick Inc. LLC
 * @license    http://store.delorumcommerce.com/license/commercial-extension
 */
class TinyBrick_OrderEdit_Model_Order_Address_Total_Discount extends TinyBrick_OrderEdit_Model_Order_Address_Total_Abstract
{
    public function collect(TinyBrick_OrderEdit_Model_Order_Address $address)
    {

        $order = $address->getOrder();
        
        $eventArgs = array(
            'website_id'=>Mage::app()->getStore($order->getStoreId())->getWebsiteId(),
            'customer_group_id'=>$order->getCustomerGroupId(),
            'coupon_code'=>$order->getCouponCode(),
        );

        $address->setFreeShipping(0);
        $totalDiscountAmount = 0;
        $subtotalWithDiscount= 0;
        $baseTotalDiscountAmount = 0;
        $baseSubtotalWithDiscount= 0;

        $items = $order->getOrderItems();

		        
        
        if (!count($items)) {

            $address->setDiscountAmount($totalDiscountAmount);
            $address->setSubtotalWithDiscount($subtotalWithDiscount);
            $address->setBaseDiscountAmount($baseTotalDiscountAmount);
            $address->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);
            return $this;
        }

        $hasDiscount = false;
        
        
        foreach ($items as $item) {

            if ($item->getNoDiscount()) {
                $item->setDiscountAmount(0);
                $item->setBaseDiscountAmount(0);
                $item->setRowTotalWithDiscount($item->getRowTotal());
                $item->setBaseRowTotalWithDiscount($item->getRowTotal());
                $subtotalWithDiscount+=$item->getRowTotal();
                $baseSubtotalWithDiscount+=$item->getBaseRowTotal();
            }
            else {
                /**
                 * Child item discount we calculate for parent
                 */
                if ($item->getParentItemId()) {
                    continue;
                }
                               	
                $eventArgs['item'] = $item;
                //Mage::dispatchEvent('quickorderedit_order_address_discount_item', $eventArgs);

                if ($item->getDiscountAmount() || $item->getFreeShipping()) {
                    $hasDiscount = true;
                }
                $totalDiscountAmount += $item->getDiscountAmount();
                $baseTotalDiscountAmount += $item->getDiscountAmount();

                $item->setRowTotalWithDiscount($item->getRowTotal()-$item->getDiscountAmount());
                $item->setBaseRowTotalWithDiscount($item->getBaseRowTotal()-$item->getDiscountAmount());

                $subtotalWithDiscount+=$item->getRowTotalWithDiscount();
                $baseSubtotalWithDiscount+=$item->getBaseRowTotalWithDiscount();
            }
        }
        
        
        
        $order->setDiscountAmount($totalDiscountAmount);
        $order->setSubtotalWithDiscount($subtotalWithDiscount);
        $order->setBaseDiscountAmount($baseTotalDiscountAmount);
        $order->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);

        $order->setGrandTotal($order->getGrandTotal() - $order->getDiscountAmount());
        $order->setBaseGrandTotal($order->getBaseGrandTotal()-$order->getBaseDiscountAmount());
        return $this;
    }

    public function fetch(TinyBrick_OrderEdit_Model_Order_Address $address)
    {
        $amount = $address->getDiscountAmount();

        if ($amount!=0) {
            $title = Mage::helper('sales')->__('Discount');
            if ($code = $address->getCouponCode()) {
                $title = Mage::helper('sales')->__('Discount (%s)', $code);
            }
            $address->addTotal(array(
                'code'=>$this->getCode(),
                'title'=>$title,
                'value'=>-$amount
            ));
        }
        return $this;
    }

}