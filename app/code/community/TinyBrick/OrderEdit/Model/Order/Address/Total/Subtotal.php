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
class TinyBrick_OrderEdit_Model_Order_Address_Total_Subtotal extends TinyBrick_OrderEdit_Model_Order_Address_Total_Abstract
{
    /**
     * Collect address subtotal
     *
     * @param   Mage_Sales_Model_Order_Address $address
     * @return  Mage_Sales_Model_Order_Address_Total_Subtotal
     */
    public function collect(TinyBrick_OrderEdit_Model_Order_Address $address)
    {
    	
        /**
         * Reset subtotal information
         */
        $address->setSubtotal(0);
        $address->setBaseSubtotal(0);
        $address->setTotalQty(0);
        $address->setBaseTotalPriceIncTax(0);
        $address->setGrandTotal(0);
        $address->setBaseGrandTotal(0);
        
        
        
		$order = $address->getOrder();
		

        /**
         * Process address items
         */
        $items = $order->getOrderItems();
		
        foreach ($items as $item) {
            if (!$this->_initItem($address, $item) || $item->getQtyOrdered()<=0) {
                //$this->_removeItem($address, $item);
            }
        }
		
		
        /**
         * Initialize grand totals
         */ 
        
        $address->setGrandTotal($address->getSubtotal());
        $address->setBaseGrandTotal($address->getBaseSubtotal());
        
        return $this;
    }

    /**
     * Address item initialization
     *
     * @param  $item
     * @return bool
     */
    protected function _initItem($address, $item)
    {
        if ($item instanceof TinyBrick_OrderEdit_Model_Order_Address_Item) {
            $orderItem = $item->getAddress()->getItemById($item->getOrderItemId());
        } else {
            $orderItem = $item;
        }

        $product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
        


		if ($orderItem->getParentItem() && $orderItem->isChildrenCalculated()) {
            $finalPrice = $orderItem->getParentItem()->getProduct()->getPriceModel()->getChildFinalPrice(
               $orderItem->getParentItem()->getProduct(),
               $orderItem->getParentItem()->getQty(),
               $orderItem->getProduct(),
               $orderItem->getQty()
            );
            $item->setPrice($finalPrice);
            $item->calcRowTotal();
        }
        else if (!$orderItem->getParentItem()) {
	            //$finalPrice = $product->getFinalPrice($orderItem->getQtyOrdered());
	            //if($orderItem->getProductType() != 'package') {
	            //	$item->setPrice($finalPrice);
	            //}
	
	            $item->calcRowTotal();
	
	            $address->setSubtotal($address->getSubtotal() + $item->getRowTotal());
	
	            $address->setBaseSubtotal($address->getBaseSubtotal() + $item->getBaseRowTotal());
	            $address->setTotalQty($address->getTotalQty() + $item->getQtyOrdered());
        }

        return true;
    }

    /**
     * Remove item
     *
     * @param  $address
     * @param  $item
     * @return Mage_Sales_Model_Order_Address_Total_Subtotal
     */
    protected function _removeItem($address, $item)
    {
        if ($item instanceof Mage_Sales_Model_Order_Item) {
            $address->removeItem($item->getId());
            if ($address->getOrder()) {
                $address->getOrder()->removeItem($item->getId());
            }
        }
        elseif ($item instanceof TinyBrick_OrderEdit_Model_Order_Address_Item) {
            $address->removeItem($item->getId());
            if ($address->getOrder()) {
                $address->getOrder()->removeItem($item->getOrderItemId());
            }
        }

        return $this;
    }

    public function fetch(TinyBrick_OrderEdit_Model_Order_Address $address)
    {
        $address->addTotal(array(
            'code'=>$this->getCode(),
            'title'=>Mage::helper('sales')->__('Subtotal'),
            'value'=>$address->getSubtotal()
        ));
        return $this;
    }
}