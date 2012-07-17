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
}