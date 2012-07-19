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
class TinyBrick_OrderEdit_Model_Order_Address_Total_Shipping extends TinyBrick_OrderEdit_Model_Order_Address_Total_Abstract
{
	public function collect(TinyBrick_OrderEdit_Model_Order_Address $address)
    {
		$order = $address->getOrder();
        $items = $order->getAllItems();
		
        $method = $address->getOrder()->getShippingMethod();
        $freeAddress = $address->getOrder()->getFreeShipping();
        
        $addressWeight      = $address->getOrder()->getWeight();
        $addressWeight		= 0;
        
        $freeMethodWeight   = $address->getOrder()->getFreeMethodWeight();
		
        $addressQty = 0;

        foreach ($items as $item) {
            
            /**
             * Children weight we calculate for parent
             */
            if ($item->getParentItem()) {
                continue;
            }


             if ($item->getHasChildren() && $item->isShipSeparately()) {
                 foreach ($item->getChildren() as $child) {
                      if ($child->getProduct()->isVirtual()) {
                          continue;
                      }
                     $addressQty += $item->getQtyOrdered()*$child->getQtyOrdered();
 
                     if (!$item->getProduct()->getWeightType()) {
                         $itemWeight = $child->getWeight();
                         $itemQty    = $item->getQtyOrdered()*$child->getQtyOrdered();
                         $rowWeight  = $itemWeight*$itemQty;
                         $addressWeight += $rowWeight;
                         if ($freeAddress || $child->getFreeShipping()===true) {
                             $rowWeight = 0;
                         } elseif (is_numeric($child->getFreeShipping())) {
                             $freeQty = $child->getFreeShipping();
                             if ($itemQty>$freeQty) {
                                 $rowWeight = $itemWeight*($itemQty-$freeQty);
                             }
                             else {
                                 $rowWeight = 0;
                             }
                         }
                         $freeMethodWeight += $rowWeight;
                         $item->setRowWeight($rowWeight);
                     }
                 }
                 if ($item->getProduct()->getWeightType()) {
                     $itemWeight = $item->getWeight();
                     $rowWeight  = $itemWeight*$item->getQtyOrdered();
                     $addressWeight+= $rowWeight;
                     if ($freeAddress || $item->getFreeShipping()===true) {
                         $rowWeight = 0;
                     } elseif (is_numeric($item->getFreeShipping())) {
                         $freeQty = $item->getFreeShipping();
                         if ($item->getQtyOrdered()>$freeQty) {
                             $rowWeight = $itemWeight*($item->getQtyOrdered()-$freeQty);
                         }
                         else {
                             $rowWeight = 0;
                         }
                     }
                     $freeMethodWeight+= $rowWeight;
                     $item->setRowWeight($rowWeight);
                }
            }
            else {
                $itemWeight = $item->getWeight();
                $rowWeight  = $itemWeight*$item->getQtyOrdered();
                $addressWeight+= $rowWeight;
                if ($freeAddress || $item->getFreeShipping()===true) {
                    $rowWeight = 0;
                } elseif (is_numeric($item->getFreeShipping())) {
                    $freeQty = $item->getFreeShipping();
                    if ($item->getQtyOrdered()>$freeQty) {
                        $rowWeight = $itemWeight*($item->getQtyOrdered()-$freeQty);
                    }
                    else {
                        $rowWeight = 0;
                    }
                }
                $freeMethodWeight+= $rowWeight;
                $item->setRowWeight($rowWeight);
            }
        }

        if (isset($addressQty)) {
            $address->getOrder()->setItemQty($addressQty);
        }

        $address->getOrder()->setWeight($addressWeight);
        $address->getOrder()->setFreeMethodWeight($freeMethodWeight);
        
        //$address->recalculateShippingRates($address->getOrder());

        $method = $address->getOrder()->getShippingMethod();
        
        if ($method) {
            foreach ($address->getAllShippingRates() as $rate) {
                if ($rate->getCode()==$method) {
                    $amountPrice = $address->getOrder()->getStore()->convertPrice($rate->getPrice(), false);
                    $rate->setShippingAmount($amountPrice);
                    $address->getOrder()->setShippingDescription($rate->getCarrierTitle().' - '.$rate->getMethodTitle());
                    break;
                }
            }
        }
        $address->setGrandTotal($address->getGrandTotal() + $address->getOrder()->getShippingAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getOrder()->getShippingAmount());

        return $this;
    }

    public function fetch(TinyBrick_OrderEdit_Model_Order_Address $address)
    {
        $amount = $address->getOrder()->getShippingAmount();
        if ($amount!=0 || $address->getOrder()->getShippingDescription()) {
            $address->getOrder()->addTotal(array(
                'code'=>$this->getCode(),
                'title'=>Mage::helper('sales')->__('Shipping & Handling').' ('.$address->getOrder()->getShippingDescription().')',
                'value'=>$address->getOrder()->getShippingAmount()
            ));
        }
        return $this;
    }
}
