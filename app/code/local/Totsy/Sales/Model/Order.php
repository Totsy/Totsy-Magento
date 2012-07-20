<?php
/**
 * @category    Totsy
 * @package     Totsy_Sales_Model
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Sales_Model_Order extends Mage_Sales_Model_Order
{
   /**
     * This method will check the order for items that have been canceled
     *
     * @return bool
     */
    public function containsCanceledItems() {
        foreach($this->getItemsCollection() as $item) {
            if($item->getQtyCanceled()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve order cancel availability
     *
     * @return bool
     */
    public function canCancel()
    {
        if ($this->canUnhold()) {  // $this->isPaymentReview()
            return false;
        }

        $allInvoiced = true;
        foreach ($this->getAllItems() as $item) {
            if ($item->getQtyToInvoice()) {
                $allInvoiced = false;
                break;
            }
        }

        // 201207 - CJD - Added in logic so that you can cancel an order that has had the items canceled individually
        $allCanceled = true;
        foreach($this->getAllItems() as $item) {
            if(!$item->getParentItemId() && ($item->getQtyCanceled() != $item->getQtyOrdered())) {
                $allCanceled = false;
                break;
            }
        }

        if ($allInvoiced  && !$allCanceled) {
            return false;
        }

        $state = $this->getState();
        if ($this->isCanceled() || $state === self::STATE_COMPLETE || $state === self::STATE_CLOSED) {
            return false;
        }

        if ($this->getActionFlag(self::ACTION_FLAG_CANCEL) === false) {
            return false;
        }

        /**
         * Use only state for availability detect
         */
        /*foreach ($this->getAllItems() as $item) {
            if ($item->getQtyToCancel()>0) {
                return true;
            }
        }
        return false;*/
        return true;
    }

}