<?php
/**
 * @category    Totsy
 * @package     Totsy_Sales_Model_Order_Invoice_Total_Discount
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Sales_Model_Order_Invoice_Total_Discount extends Mage_Sales_Model_Order_Invoice_Total_Discount
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $invoice->setDiscountAmount(0);
        $invoice->setBaseDiscountAmount(0);

        $totalDiscountAmount     = 0;
        $baseTotalDiscountAmount = 0;

        /**
         * Checking if shipping discount was added in previous invoices.
         * So basically if we have invoice with positive discount and it
         * was not canceled we don't add shipping discount to this one.
         */
        $addShippingDiscount = true;
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
            if ($previousInvoice->getDiscountAmount() && $previousInvoice->getState() != 3) {
                $addShippingDiscount = false;
            }
        }

        if ($addShippingDiscount) {
            $totalDiscountAmount     = $totalDiscountAmount + $invoice->getOrder()->getShippingDiscountAmount();
            $baseTotalDiscountAmount = $baseTotalDiscountAmount + $invoice->getOrder()->getBaseShippingDiscountAmount();
        }

        foreach ($invoice->getAllItems() as $item) {
            if ($item->getOrderItem()->isDummy()) {
                continue;
            }
            $orderItem = $item->getOrderItem();
            $orderItemDiscount      = (float) $orderItem->getDiscountAmount();
            $baseOrderItemDiscount  = (float) $orderItem->getBaseDiscountAmount();
            $orderItemQty       = $orderItem->getQtyOrdered();

            if ($orderItemDiscount && $orderItemQty) {
                /**
                 * Resolve rounding problems
                 */
                if ($item->isLast()) {
                    $discount = $orderItemDiscount - $orderItem->getDiscountInvoiced();
                    $baseDiscount = $baseOrderItemDiscount - $orderItem->getBaseDiscountInvoiced();
                }
                else {
                    $discount = $orderItemDiscount*$item->getQty()/$orderItemQty;
                    $baseDiscount = $baseOrderItemDiscount*$item->getQty()/$orderItemQty;

                    $discount = $invoice->getStore()->roundPrice($discount);
                    $baseDiscount = $invoice->getStore()->roundPrice($baseDiscount);
                }

                $item->setDiscountAmount($discount);
                $item->setBaseDiscountAmount($baseDiscount);

                $totalDiscountAmount += $discount;
                $baseTotalDiscountAmount += $baseDiscount;
            }
        }


        $invoice->setDiscountAmount($totalDiscountAmount);
        $invoice->setBaseDiscountAmount($baseTotalDiscountAmount);

        $invoice->setGrandTotal($invoice->getGrandTotal() - $totalDiscountAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseTotalDiscountAmount);
        return $this;
    }
}