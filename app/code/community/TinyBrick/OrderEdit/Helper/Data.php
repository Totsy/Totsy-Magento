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
class TinyBrick_OrderEdit_Helper_Data extends Mage_Core_Helper_Data
{
	const MAXIMUM_AVAILABLE_NUMBER = 99999999;
	
	public function checkQuoteAmount(Mage_Sales_Model_Order $order, $amount)
	{
		if (!$order->getHasError() && ($amount>=self::MAXIMUM_AVAILABLE_NUMBER)) {
			$order->setHasError(true);
			$order->addMessage(
		    $this->__('Some items have quantities exceeding allowed quantities. Please select a lower quantity to checkout.')
		);
	}
		return $this;
	}

    public function getItemQuantitiesAvailable($item) {
        $maxAvailable = ((int)$item->getQtyOrdered()
            + (int) Mage::getModel('catalog/product')->load($item->getProductId())->getStockItem()->getQty());
        if($maxAvailable > 9) {
            $maxAvailable = 9;
        }
        return $maxAvailable;
    }

    public function checkItemAvailability($product, $qty) {
        $stockItem = $product->getStockItem();
        if ($stockItem && $stockItem->getIsQtyDecimal()) {
            $product->setIsQtyDecimal(1);
        }
        $oldItemQty = 0;
        if($this->getSession()->getOrder()) {
            $oldOrderItems = $this->getSession()->getOrder()->getItemsCollection();
            foreach ($oldOrderItems as $oldItem){
                if($oldItem->product_id == $product->getId()) {
                    $oldItemQty = (int)$oldItem->getQtyOrdered();
                }
            }
        }
        if($qty > ((int) $stockItem->getQty() + $oldItemQty)) {
            return false;
        } else {
            return true;
        }
    }
}

