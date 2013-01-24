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
class Harapartners_Fulfillmentfactory_Model_Service_Itemqueue
{    
    /**
     * save item queue from an order
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function saveFromOrder($order) {
        if(!empty($order) && !!$order->getId()) {
            $orderId = $order->getId();
            $incrementId = $order->getIncrementId();
            $storeId = $order->getStoreId();
            
            $items = $order->getAllItems();
            //save shipping address as a customer address
            $addressCustomer = Mage::getModel('customer/address');
            $customerId = $order->getQuote()->getCustomerId();
            if($order->getShippingAddress()) {
                $shippingAddressDatas = $order->getShippingAddress()->getData();
                unset($shippingAddressDatas['entity_id']);
                $addressCustomer->setData($shippingAddressDatas)
                    ->setCustomerId($customerId)
                    ->setIsDefaultBilling(false)
                    ->setIsDefaultShipping(false)
                    ->save();
            }
            foreach($items as $item) {
                //Duplicate test, if order_item_id exist, load the existing one, otherwise return an empty object
                $itemQueue = Mage::getModel('fulfillmentfactory/itemqueue')->loadByItemId($item->getId());
                
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                if($product->getTypeId() == 'simple') {
                    $itemQueue->setOrderItemId($item->getItemId());
                    $itemQueue->setProductId($item->getProductId());
                    $itemQueue->setSku($item->getSku());
                    $itemQueue->setName($item->getName());
                     
                    $itemQueue->setOrderId($orderId);
                    $itemQueue->setOrderIncrementId($incrementId);
                    $itemQueue->setStoreId($storeId);
                     
                    $itemQueue->setQtyBackordered($item->getQtyBackordered());
                    $itemQueue->setQtyCanceled($item->getQtyCanceled());
                    $itemQueue->setQtyInvoiced($item->getQtyInvoiced());
                    $itemQueue->setQtyOrdered($item->getQtyOrdered());
                    $itemQueue->setQtyRefunded($item->getQtyRefunded());
                    $itemQueue->setQtyShipped($item->getQtyShipped());
                    
                    if(!!$item->getOriginalQuoteItemId()) {
                        $itemQueue->setOriginalQuoteItemId($item->getOriginalQuoteItemId());
                    }
                    else {
                        $itemQueue->setOriginalQuoteItemId($item->getId());
                    }
                     
                    $itemQueue->setFulfillCount(0);
                    $itemQueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PENDING);
                     
                    $itemQueue->save();
                }
            }
        }
    }
    
    /**
     * update Itemqueue status base on order's status change
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function updateItemQueueStatusByOrder($order) {        
        $state = $order->getState();
        $status = $order->getStatus();
        $collection = Mage::getModel('fulfillmentfactory/itemqueue')->getCollection()->loadByOrderId($order->getId());
        
        //use status instead of state!
        //to avoid the status is not consistent with state problem!
        if($status == 'complete') {
            foreach($collection as $itemqueue) {
                if($itemqueue->getStatus() != Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED) {
                    $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CLOSED);
                    $itemqueue->save();
                }
            }
        }
        else if($status == 'pending' || $status == 'processing'){
            foreach($collection as $itemqueue) {
                if($itemqueue->getStatus() != Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED) {
                    if ($itemqueue->getFulfillCount() == $itemqueue->getQtyOrdered()) {
                        $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_READY);
                    } else if (0 == $itemqueue->getFulfillCount()) {
                        $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PENDING);
                    } else {
                        $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PARTIAL);
                    }

                    $itemqueue->save();
                }
            }
            
            if($state != Mage_Sales_Model_Order::STATE_PROCESSING) {
            	$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
            	$order->save();
            }
        }
        else if($status == 'canceled'){
            foreach($collection as $itemqueue) {
                $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED);
                $itemqueue->save();
            }
            
        	if($state != Mage_Sales_Model_Order::STATE_CANCELED) {
            	$order->setState(Mage_Sales_Model_Order::STATE_CANCELED);
            	$order->save();
            }
        }
        else if($status == 'holded' || $status == Totsy_Sales_Model_Order::STATUS_BATCH_CANCEL_CSR_REVIEW){
            foreach($collection as $itemqueue) {
                if($itemqueue->getStatus() != Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED) {
                    $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUSPENDED);
                    $itemqueue->save();
                }
            }

			if($state != Mage_Sales_Model_Order::STATE_HOLDED && $status == 'holded') {
            	$order->setState(Mage_Sales_Model_Order::STATE_HOLDED);
            	$order->save();
            }
        }
        else if($status == Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PROCESSING_FULFILLMENT){
            foreach($collection as $itemqueue) {
                if($itemqueue->getStatus() != Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED) {
                    $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUBMITTED);
                    $itemqueue->save();
                }
            }
        }
        else if($status == Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_FULFILLMENT_FAILED){
            foreach($collection as $itemqueue) {
                if($itemqueue->getStatus() != Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED) {
                    $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUSPENDED);
                    $itemqueue->save();
                }
            }
        } 
        else if($status == Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED){
            foreach($collection as $itemqueue) {
                if($itemqueue->getStatus() != Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED) {
                    $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUSPENDED);
                    $itemqueue->save();
                }
            }
        }
        else if($status == Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_SHIPMENT_AGING){
            foreach($collection as $itemqueue) {
                if($itemqueue->getStatus() != Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED) {
                    $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUBMITTED);
                    $itemqueue->save();
                }
            }
        }
    }

    /**
     * remove all itemqueue objects which belong to one order
     *
     * @param int $orderId
     */
    public function cancelItemqueueByOrderId($orderId) {
        $collection = Mage::getModel('fulfillmentfactory/itemqueue')->getCollection()->loadByOrderId($orderId);
        
        foreach($collection as $itemqueue) {
            $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED);
            $itemqueue->save();
        }
    }

    public function cancelItemqueueByOrderItemId($orderItemId) {
        $collection = Mage::getModel('fulfillmentfactory/itemqueue')->getCollection()->loadByOrderItemId($orderItemId);

        foreach($collection as $itemqueue) {
            $itemqueue = Mage::getModel('fulfillmentfactory/itemqueue')->load($itemqueue->getId());
            $itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED);
            $itemqueue->save();
        }
    }
}
