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
class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Nitems extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
	public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array())
	{
		$comment = "";
		foreach($data['sku'] as $key => $sku) {
			$qty = $data['qty'][$key];
			
			$product = Mage::getModel('catalog/product')->getCollection()
				->addAttributeToFilter('sku', $sku)
				->addAttributeToSelect('*')
				->getFirstItem();
			
			$quoteItem = Mage::getModel('sales/quote_item')->setProduct($product)
				->setQuote(Mage::getModel('sales/quote')->load($order->getQuoteId()));
			
			$orderItem = Mage::getModel('sales/convert_quote')->itemToOrderItem($quoteItem)->setProduct($product);
			$productPrice = $data['price'][$key];
			
			$orderItem->setPrice($productPrice);
			$orderItem->setBasePrice($productPrice);
			$orderItem->setBaseOriginalPrice($productPrice);
			$orderItem->setOriginalPrice($productPrice);
			$orderItem->setQtyOrdered($qty);
			if($data['discount'][$key]) {
				$orderItem->setDiscountAmount($data['discount'][$key]);
			} else {
				$orderItem->setDiscountAmount(0);
			}
			$orderItem->setOrderId($order->getId());
			
			$orderItem->setOrder($order);
			$orderItem->save();
			$order->addItem($orderItem);
			$order->save();
			$comment .= "Added item(SKU): " . $sku . "<br />";
		}
		if($comment != "") {
			$comment = "Added new items:<br />" . $comment;
			return $comment;
		}
		return true;
	}
}