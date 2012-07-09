<?php
/**
 * @category    Totsy
 * @package     Totsy_Sales_Helper
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Sales_Helper_Order
{
    /**
     * The number of days to add to an initial date to calculate estimated
     * shipping dates.
     *
     * @var int
     */
    protected $_shipDateIncrement = 21; // 15 business days

    /**
     * Calculate the estimated ship date for an Order or Quote object, by
     * inspecting each item to find the latest event end date.
     * Then add a fixed number to the result.
     *
     * @param $order Mage_Sales_Model_Quote|Mage_Sales_Model_Order
     * @return int The Unix timestamp of the estimated ship date
     */
    public function calculateEstimatedShipDate($order)
    {
        // use the creation date for the order or quote
        $shipDate = strtotime($order->getCreatedAt());
        if($order->getRelationParentId()) {
            $parentOrder = Mage::getModel('sales/order')->load($order->getRelationParentId());
            $shipDate = strtotime($parentOrder->getCreatedAt());
            do {
                $parentOrder = Mage::getModel('sales/order')->load($parentOrder->getRelationParentId());
                if($parentOrder->getCreatedAt()) {
                    $shipDate = strtotime($parentOrder->getCreatedAt());
                }
            } while($parentOrder->getRelationParentId());
        }
        // increment the ship date to the end date of the of the event that
        // ends last, in the collection of events
        $items = $order->getItemsCollection();
        foreach ($items as $orderItem) {
            if ($orderItem->getParentItem()) {
                continue;
            }

            $product = Mage::getModel('catalog/product')->load(
                $orderItem->getProductId()
            );
            $categories = $product->getCategoryCollection();
            foreach ($categories as $category) {
                $categoryEndDate = strtotime($category->getEventEndDate());
                $shipDate = max($shipDate, $categoryEndDate);
            }
        }

        $shipDate += 24 * 3600 * $this->_shipDateIncrement;

        // when the calculated ship date falls on a weekend, bump it forward
        // to the following weekday
        if (date('N', $shipDate) > 5) {
            $shipDate += 24 * 3600 * (8 - date('N', $shipDate));
        }

        return $shipDate;
    }

    /**
     * Calculate the estimated total savings for an Order or Quote object, by
     * inspecting the produce price difference for each item.
     *
     * @param $order Mage_Sales_Model_Quote|Mage_Sales_Model_Order
     * @return float The estimated total savings.
     */
    public function calculateEstimatedSavings($order)
    {
        $savings = 0;

        $items = $order->getItemsCollection();
        foreach ($items as $orderItem) {
            if ($orderItem->getParentItem()) {
                continue;
            }

            $product = Mage::getModel('catalog/product')->load(
                $orderItem->getProductId()
            );

            $productDiscount = $product->getPrice() - $product->getSpecialPrice();
            if($orderItem->getQty()){
	            $savings += $productDiscount * $orderItem->getQty();
            }
            elseif($orderItem->getQtyOrdered()){
	            $savings += $productDiscount * $orderItem->getQtyOrdered();
            }
            else{
	            $savings += $productDiscount;
            }
        }

        return $savings;
    }
}
