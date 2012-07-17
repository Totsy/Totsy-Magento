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
     * @return self instatnce
     */
    public function updateItemQueueStatus(Varien_Event_Observer $observer) {
        try {
            $order = $observer->getEvent()->getOrder();
            Mage::getModel('fulfillmentfactory/service_itemqueue')->updateItemQueueStatusByOrder($order);
        }
        catch(Exception $e) {
            Mage::logException($e);
        }
        
        return $this;
    }
    
    /**
     * Cancel item queue objects by order
     *
     * @param Varien_Event_Observer $observer
     * @return self instatnce
     */
    public function cancelItemQueue(Varien_Event_Observer $observer){
        try {
            $order = $observer->getEvent()->getOrder();
            if(!!$order && !!$order->getId()){
                Mage::getModel('fulfillmentfactory/service_itemqueue')->cancelItemqueueByOrderId($order->getId());
            }
        }
        catch(Exception $e) {
            Mage::logException($e);
        }
        
        return $this;
    }

    function updateItemQueueAfterItemSave(Varien_Event_Observer $observer) {
        try {
            $event = $observer->getEvent();


            $orderItem = $event->getDataObject();
            if(!!$orderItem && !!$orderItem->getId()){
                Mage::getModel('fulfillmentfactory/service_itemqueue')->cancelItemqueueByOrderItemId($orderItem->getId());
            }
        } catch(Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }
}    
