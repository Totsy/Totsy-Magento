<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Fulfillmentfactory_Model_Observer
{
    /**
     * update Itemqueue status base on order's status change
     *
     * @param Varien_Event_Observer $observer
     * @return Harapartners_Fulfillmentfactory_Model_Observer
     */
    public function updateItemQueueStatus(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        try {
            Mage::getModel('fulfillmentfactory/service_itemqueue')
                ->updateItemQueueStatusByOrder($order);
        } catch(Exception $e) {
            Mage::logException($e);
        }

        if ('processing_fulfillment' == $order->getStatus() ||
            'complete' == $order->getStatus() ||
            'canceled' == $order->getStatus()
        ) {
            Mage::helper('fulfillmentfactory/log')
                ->removeErrorLogEntriesForOrder($order);
        }

        return $this;
    }

    /**
     * Cancel item queue objects by order
     *
     * @param Varien_Event_Observer $observer
     * @return Harapartners_Fulfillmentfactory_Model_Observer
     */
    public function cancelItemQueue(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        try {
            Mage::getModel('fulfillmentfactory/service_itemqueue')
                ->cancelItemqueueByOrderId($order->getId());
        } catch(Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    function updateItemQueueAfterItemSave(Varien_Event_Observer $observer)
    {
        $orderItem = $observer->getEvent()->getDataObject();

        try {
            if ($orderItem->getParentItemId()) {
                return $this;
            }

            if ($orderItem && $orderItem->getId() &&
                $orderItem->getStatusId() === Mage_Sales_Model_Order_Item::STATUS_CANCELED
            ) {
                Mage::getModel('fulfillmentfactory/service_itemqueue')
                    ->cancelItemqueueByOrderItemId($orderItem->getId());

                foreach ($orderItem->getChildrenItems() as $childItem) {
                    Mage::getModel('fulfillmentfactory/service_itemqueue')
                        ->cancelItemqueueByOrderItemId($childItem->getId());
                }
            }
        } catch(Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }
}
