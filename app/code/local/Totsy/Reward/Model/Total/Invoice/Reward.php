<?php
/**
 * @category    Totsy
 * @package     Totsy_Reward_Model
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Reward_Model_Total_Invoice_Reward
    extends Enterprise_Reward_Model_Total_Invoice_Reward
{
    /**
     * Collect reward total for invoice
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @return Enterprise_Reward_Model_Total_Invoice_Reward
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $previousInvoiceFailed = false;
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
            if ($previousInvoice->getState() == 3) {
                $previousInvoiceFailed = true;
            }
        }
        $order = $invoice->getOrder();
        if($previousInvoiceFailed) {
            $rewardCurrencyAmountLeft = $order->getRewardCurrencyAmount();
            $baseRewardCurrencyAmountLeft = $order->getBaseRewardCurrencyAmount();
        } else {
            $rewardCurrencyAmountLeft = $order->getRewardCurrencyAmount() - $order->getRewardCurrencyAmountInvoiced();
            $baseRewardCurrencyAmountLeft = $order->getBaseRewardCurrencyAmount() - $order->getBaseRewardCurrencyAmountInvoiced();
        }
        if ($order->getBaseRewardCurrencyAmount() && $baseRewardCurrencyAmountLeft > 0) {
            if ($baseRewardCurrencyAmountLeft < $invoice->getBaseGrandTotal()) {
                $invoice->setGrandTotal($invoice->getGrandTotal() - $rewardCurrencyAmountLeft);
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseRewardCurrencyAmountLeft);
            } else {
                $rewardCurrencyAmountLeft = $invoice->getGrandTotal();
                $baseRewardCurrencyAmountLeft = $invoice->getBaseGrandTotal();

                $invoice->setGrandTotal(0);
                $invoice->setBaseGrandTotal(0);
            }
            $pointValue = $order->getRewardPointsBalance() / $order->getBaseRewardCurrencyAmount();
            $rewardPointsBalance = $baseRewardCurrencyAmountLeft*ceil($pointValue);
            $rewardPointsBalanceLeft = $order->getRewardPointsBalance() - $order->getRewardPointsBalanceInvoiced();
            if ($rewardPointsBalance > $rewardPointsBalanceLeft) {
                $rewardPointsBalance = $rewardPointsBalanceLeft;
            }
            $invoice->setRewardPointsBalance($rewardPointsBalance);
            $invoice->setRewardCurrencyAmount($rewardCurrencyAmountLeft);
            $invoice->setBaseRewardCurrencyAmount($baseRewardCurrencyAmountLeft);
        }
        return $this;
    }
}
