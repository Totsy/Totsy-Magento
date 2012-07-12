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
class TinyBrick_OrderEdit_Model_Order_Item extends Mage_Sales_Model_Order_Item
{	
	protected function _construct()
    {
        $this->_init('orderedit/order_item');
    }
	public function calcRowTotal()
    {
		$qty = $this->getQtyOrdered();
		
        if ($this->getParentItem()) {
            $qty = $qty*$this->getParentItem()->getQtyOrdered();
        }

        if ($rowTotal = $this->getRowTotalExcTax()) {
            $baseTotal = $rowTotal;
            $total = $this->getStore()->convertPrice($baseTotal);
        }
        else {

            $total      = $this->getCalculationPrice()*$qty;
            $baseTotal  = $this->getBaseCalculationPrice()*$qty;
        }

        $this->setRowTotal($total);

        $this->setBaseRowTotal($this->getStore()->roundPrice($baseTotal));
		$this->save();
        return $this;

    }
    
    public function getStore()
    {
        return $this->getOrder()->getStore();
    }
    
    public function getCalculationPrice()
    {
    	if($this->getProductType() != 'package') {
	        $price = $this->getData('calculation_price');
	        if (is_null($price)) {
	            if ($this->hasCustomPrice()) {
	                $price = $this->getCustomPrice();
	            } else {
	                $price = $this->getOriginalPrice();
	            }
	            $this->setData('calculation_price', $price);
	        }
	    } else {
	    	$price = $this->getPrice();
	    }
	        return $price;
    }
	
	public function calcTaxAmount()
    {
        $store = $this->getStore();

        if (!Mage::helper('tax')->priceIncludesTax($store)) {
            if (Mage::helper('tax')->applyTaxAfterDiscount($store)) {
                $rowTotal       = $this->getRowTotalWithDiscount();
                $rowBaseTotal   = $this->getBaseRowTotalWithDiscount();
            } else {
                $rowTotal       = $this->getRowTotal();
                $rowBaseTotal   = $this->getBaseRowTotal();
            }

            $taxPercent = $this->getTaxPercent()/100;

            $this->setTaxAmount($store->roundPrice($rowTotal * $taxPercent));
            $this->setBaseTaxAmount($store->roundPrice($rowBaseTotal * $taxPercent));

            $rowTotal       = $this->getRowTotal();
            $rowBaseTotal   = $this->getBaseRowTotal();
            $this->setTaxBeforeDiscount($store->roundPrice($rowTotal * $taxPercent));
            $this->setBaseTaxBeforeDiscount($store->roundPrice($rowBaseTotal * $taxPercent));
        } else {
            if (Mage::helper('tax')->applyTaxAfterDiscount($store)) {
                $totalBaseTax = $this->getBaseTaxAmount();
                $totalTax = $this->getTaxAmount();

                if ($totalTax && $totalBaseTax) {
                    $totalTax -= $this->getDiscountAmount()*($this->getTaxPercent()/100);
                    $totalBaseTax -= $this->getBaseDiscountAmount()*($this->getTaxPercent()/100);

                    $this->setBaseTaxAmount($store->roundPrice($totalBaseTax));
                    $this->setTaxAmount($store->roundPrice($totalTax));
                }
            }
        }

        if (Mage::helper('tax')->discountTax($store) && !Mage::helper('tax')->applyTaxAfterDiscount($store)) {
            if ($this->getDiscountPercent()) {
                $baseTaxAmount =  $this->getBaseTaxBeforeDiscount();
                $taxAmount = $this->getTaxBeforeDiscount();

                $baseDiscountDisposition = $baseTaxAmount/100*$this->getDiscountPercent();
                $discountDisposition = $taxAmount/100*$this->getDiscountPercent();

                $this->setDiscountAmount($this->getDiscountAmount()+$discountDisposition);
                $this->setBaseDiscountAmount($this->getBaseDiscountAmount()+$baseDiscountDisposition);
            }
        }

        return $this;
    }

	public function getBaseCalculationPrice()
    {
        if (!$this->hasBaseCalculationPrice()) {
            if ($this->hasCustomPrice()) {
                if ($price = (float) $this->getCustomPrice()) {
                    $rate = $this->getStore()->convertPrice($price) / $price;
                    $price = $price / $rate;
                }
                else {
                    $price = $this->getCustomPrice();
                }
            } else {

                $price = $this->getPrice();
            }
            $this->setBaseCalculationPrice($price);
        }
        return $this->getData('base_calculation_price');
    }
}