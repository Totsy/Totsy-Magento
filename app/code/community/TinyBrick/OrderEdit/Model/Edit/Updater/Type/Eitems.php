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
class TinyBrick_OrderEdit_Model_Edit_Updater_Type_Eitems extends TinyBrick_OrderEdit_Model_Edit_Updater_Type_Abstract
{
	public function edit(TinyBrick_OrderEdit_Model_Order $order, $data = array())
	{
		$comment = "";
        $itemCounter = 0;
        $discountFixed = null;
        if($order->getDiscountDescription()) {
            $rule = Mage::getModel('salesrule/rule');
            $labels = $rule->getStoreLabels();
            $ruleLabel = Mage::getModel('salesrule/rule_label')->load($order->getDiscountDescription(), 'description');
            $rule = Mage::getModel('salesrule/rule_label')->load($ruleLabel->getRuleId());
            if($rule->getSimpleAction == 'cart_fixed') {
                $discountFixed = $rule->getDiscountAmount();
            }
        }

		foreach($data['id'] as $key => $itemId) {
            $itemCounter++;
			$item = $order->getItemById($itemId);
			if($data['remove'][$key]) {
				$comment .= "Removed Item(SKU): " . $item->getSku() . "<br />";
                //Changing Status of Item Queue
                if(Mage::getModel('sales/order_item')->load($itemId,'parent_item_id')->getId()) {
                    $itemQueueItemId = Mage::getModel('sales/order_item')->load($itemId,'parent_item_id')->getId();
                    $childItemId = $itemQueueItemId;
                } else {
                    $itemQueueItemId = $itemId;
                }
                $itemQueue = Mage::getModel('fulfillmentfactory/itemqueue')->loadByItemId($itemQueueItemId);
                $itemQueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED)
                          ->save();
                Mage::getSingleton('cataloginventory/stock')->backItemQty($item->getProductId(),$item->getQtyOrdered());
                $order->removeItem($itemId);
                if($childItemId) {
                    $order->removeItem($childItemId);
                }
			} else {
				$oldArray = array('price'=>$item->getPrice(), 'discount'=>$item->getDiscountAmount(), 'qty'=>$item->getQtyOrdered());
				$item->setPrice($data['price'][$key]);
				$item->setBasePrice($data['price'][$key]);
				$item->setBaseOriginalPrice($data['price'][$key]);
				$item->setOriginalPrice($data['price'][$key]);
				$item->setBaseRowTotal($data['price'][$key]);
                if($discountFixed) {
                    if($itemCounter == 1) {
                        $item->setDiscountAmount($discountFixed);
                    }
                } else {
                    if($data['discount'][$key]) {
                        $item->setDiscountAmount($data['discount'][$key]);
                    }
                }
				if($data['qty'][$key]) {
					$item->setQtyOrdered($data['qty'][$key]);
				}
				$item->save();
                if((int)$oldArray['qty'] > (int)$data['qty'][$key]) {
                    $returnedQuantity = ((int)$oldArray['qty'] - (int)$data['qty'][$key]);
                    Mage::getSingleton('cataloginventory/stock')->backItemQty($item->getProductId(),$returnedQuantity);
                } else {
                    Mage::getSingleton('cataloginventory/stock')->backItemQty($item->getProductId(),(int)$oldArray['qty']);
                    Mage::getSingleton('cataloginventory/stock')->registerItemSale($item);
                }
				$newArray = array('price'=>$item->getPrice(), 'discount'=>$item->getDiscountAmount(), 'qty'=>$item->getQtyOrdered());
				if($newArray['price'] != $oldArray['price'] || $newArray['discount'] != $oldArray['discount'] || $newArray['qty'] != $oldArray['qty']) {
					$comment = "Edited item " . $item->getSku() . "<br />";
					if($newArray['price'] != $oldArray['price']) {
						$comment .= "Price FROM: " . $oldArray['price'] . " TO: " . $newArray['price'] . "<br />";
					}
					if($newArray['discount'] != $oldArray['discount']) {
						$comment .= "Discount FROM: " . $oldArray['discount'] . " TO: " . $newArray['discount'] . "<br />";
					}
					if($newArray['qty'] != $oldArray['qty']) {
						$comment .= "Qty FROM: " . $oldArray['qty'] . " TO: " . $newArray['qty'] . "<br />";
					}
				}
			}
		}
		if($comment != "") {
			$comment = "Edited items:<br />" . $comment . "<br />";
			return $comment;
		}
		return true;
	}
}