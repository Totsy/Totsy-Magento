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
class Harapartners_Fulfillmentfactory_Model_Service_Fulfillment
{
    const DAY_SECONDS = 86400;
    
    /**
     * mark orders which havn't been fulfilled
     *
     */
    public function markFulfillmentAgingOrders() {
        $agingDay = Mage::getStoreConfig('fulfillmentfactory_options/aging_setting/fulfillment_aging_day');

        $expiredTime = time() - self::DAY_SECONDS * $agingDay;
        $expiredDate = date('Y-m-d H:i:s', $expiredTime);
        
        $orderCollection = Mage::getModel('sales/order')->getCollection()
                                                        ->addAttributeToFilter('status', 'pending')
                                                        ->addAttributeToFilter('created_at', array('to' => $expiredDate));
        foreach($orderCollection as $order) {
            $order->setStatus(Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_FULFILLMENT_AGING)
                  ->save();
        }
    }
    
    /**
     * mark orders which have sent to fulfillment but haven't received shipment infomation
     *
     */
    public function markShipmentAgingOrders() {
        $agingDay = Mage::getStoreConfig('fulfillmentfactory_options/aging_setting/shipment_aging_day');

        $expiredTime = time() - self::DAY_SECONDS * $agingDay;
        $expiredDate = date('Y-m-d H:i:s', $expiredTime);
        
        $orderCollection = Mage::getModel('sales/order')->getCollection()
                                                        ->addAttributeToFilter('status', 'processing')
                                                        ->addAttributeToFilter('updated_at', array('to' => $expiredDate));        
        foreach($orderCollection as $order) {
            $order->setStatus(Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_SHIPMENT_AGING)
                  ->save();
        }
    }
    
    /**
     * update items' fulfill statuses for all waiting orders
     *
     * @param array $availableProducts
        $availableProduct = array (
            [0] => array (
                'sku' => '1A2B3C',    //product sku
                'qty' => 100        //available number of products
            ),
            [1] => array (
                'sku' => '4D5E6F',    //product sku
                'qty' => 20        //available number of products
            ),
            etc......
        );
    */
    public function stockUpdate($availableProducts = array(), $keepTrackAffctedOrders = false) {
        $processingOrderCollection = array();
        
        foreach($availableProducts as $aProduct) {
            
            if(!isset($aProduct['sku']) || !isset($aProduct['qty']) || $aProduct['qty'] <= 0){
                continue;
            }
            
            $availableQty = $aProduct['qty'];
            //get unprocessed ItemQueue collection of this product, based on $aProduct['sku']
            $itemQueueCollection = Mage::getModel('fulfillmentfactory/itemqueue')->getCollection()
                    ->loadIncompleteItemQueueByProductSku($aProduct['sku'], $availableQty);
            
            foreach($itemQueueCollection as $itemQueue) {
                //check if we still has available products
                if($availableQty <= 0) {
                    break;
                }
                
                //if product is enough, fulfill products for item.
                //if it is not enough, fulfill the rest of the products.
                //consider partial ready items
                $needItemsCount = $itemQueue->getQtyOrdered() - $itemQueue->getFulfillCount();
                
                if($needItemsCount <= $availableQty) {
                    $itemQueue->setFulfillCount($itemQueue->getQtyOrdered());
                    $itemQueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_READY);
                    
                    $availableQty -= $needItemsCount;
                }
                else {
                    $itemQueue->setFulfillCount($itemQueue->getFulfillCount() + $availableQty);
                    $itemQueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PARTIAL);
                    
                    $availableQty = 0;
                }
                
                //save item queue object
                $itemQueue->save();
                
                if(!!$keepTrackAffctedOrders){
	                $order = Mage::getModel('sales/order')->load($itemQueue->getOrderId());
	                Mage::helper('fulfillmentfactory')->_pushUniqueOrderIntoArray($processingOrderCollection, $order);
                }
                
                unset($itemQueue);
                
            }
            unset($itemQueueCollection);
        }
        
        return $processingOrderCollection;
    }
    
    /**
     * batch cancel item queue objects
     *
     * @param array $cancelIdList    itemqueue item list
     * @return bool
     */
    public function batchCancel($cancelIdList) {
        $orderArray = array();
        $errorArray = array();
        
        //group item queues by order
        foreach($cancelIdList as $id) {
            $itemQueue = Mage::getModel('fulfillmentfactory/itemqueue')->load($id);
            
            //vaidation
            if(!in_array($itemQueue->getStatus(),
                array(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PENDING,
                Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUSPENDED))){
            	$errorArray[] = sprintf('Cannot cancel item #%d. Only pending or suspended items can be cancelled.', $itemQueue->getId());
            	continue;
            }
            
            $orderId = $itemQueue->getOrderId();
            if(!empty($orderId)) {
                if(isset($orderArray[$orderId])) {
                    $orderArray[$orderId][] = $itemQueue;
                }
                else {
                    $orderArray[$orderId] = array($itemQueue);
                }
            }
        }
        
        $isSuccess = true;
        
        //cancel/split orders
        foreach($orderArray as $orderId => $itemQueueList) {
        	try{
        		$this->_cancelItemqueue($orderId, $itemQueueList);
	            $order = Mage::getModel('sales/order')->load($orderId);
	            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
	            $email = $order->getCustomerEmail();
	            $sender = 'sales';
	            $storeId = $order->getStoreId();
	            $templateId = Mage::getModel('core/email_template')->loadByCode('_trans_Batch_Cancel')->getId(); 
				Mage::getModel('core/email_template')
				          ->sendTransactional($templateId, $sender, $email, NULL, array('customer'=>$customer, 'order'=>$order, 'item'=>$itemQueueList[0]), $storeId);
        	}catch (Exception $e){
        		$errorArray[] = $e->getMessage();
                $isSuccess = false;
        	}
        }
        
        return $errorArray;
    }
    
    /**
     * cancel/split orders by updated items
     *
     * @param array $updateItemQueueIdList
     * @return bool    indicate if new order has been created
     */
    protected function _cancelItemqueue($orderId, $updateItemQueueIdList)
    {
        $order = Mage::getModel('sales/order')->load($orderId);

        $itemsToCancel = array();
        $remainingOrderItems = false;

        foreach ($order->getItemsCollection() as $orderItem) {
            if ($orderItem->getParentItemId()) {
                continue;
            }
            $shouldBeRemoved = false;
            foreach ($updateItemQueueIdList as $itemQueueId) {
                if ($orderItem->getId() == $itemQueueId->getOrderItemId()) {
                    $shouldBeRemoved = true;
                }
                foreach ($orderItem->getChildrenItems() as $childItem) {
                    if ($childItem->getId() == $itemQueueId->getOrderItemId()) {
                        $shouldBeRemoved = true;
                    }
                }
                if ($shouldBeRemoved) {
                    $itemsToCancel[] = $orderItem;
                }
                if (!$shouldBeRemoved) {
                    $remainingOrderItems = true;
                }
            }
        }
        //cancel orders with nothing available
        if (!$remainingOrderItems) {
            $order->cancel()->save()->addStatusHistoryComment(Mage::helper('core')->__('Order Canceled by Batch Cancel Process'), false)->save();
            return true;
        }

        foreach($itemsToCancel as $item) {
            $order->addStatusHistoryComment(Mage::helper('core')->__('Item ' . $item->getSku() . ' canceled by Batch Cancel Process'), false);
            $item->cancel();
            foreach ($item->getChildrenItems() as $childItem) {
                $childItem->cancel();
                $childItem->save();
            }
            $item->save();
        }

        //Let's see if we need to cancel the order outright and if not, we need to get the various totals to update the order
        $shouldCancel = true;
        $subtotalCanceled = 0;
        $baseSubtotalCanceled = 0;
        $taxCanceled = 0;
        $baseTaxCanceled = 0;
        $shippingCanceled = 0;
        $baseShippingCanceled = 0;
        $discountCanceled = 0;
        $baseDiscountCanceled = 0;
        $totalCanceled = 0;
        $baseTotalCanceled = 0;
        foreach($order->getItemsCollection() as $item) {
            if($item->getParentItemId()) {
                continue;
            }
            if($shouldCancel && ($item->getStatusId() != Mage_Sales_Model_Order_Item::STATUS_CANCELED)) {
                $shouldCancel = false;
                break;
            }
            $subtotalCanceled += $item->getRowTotal();
            $baseSubtotalCanceled += $item->getBaseRowTotal();
            $taxCanceled += ($item->getRowTotalInclTax() - $item->getRowTotal());
            $baseTaxCanceled += ($item->getBaseRowTotalInclTax() - $item->getBaseRowTotal());
            $discountCanceled += $item->getDiscountAmount();
            $baseDiscountCanceled += $item->getBaseDiscountAmount();
            $totalCanceled += $item->getRowTotal() + ($item->getRowTotalInclTax() - $item->getRowTotal());
            $baseTotalCanceled += $item->getBaseRowTotal() + ($item->getBaseRowTotalInclTax() - $item->getBaseRowTotal());
        }
        if($shouldCancel) {
            $order->cancel()->save()->addStatusHistoryComment(Mage::helper('core')->__('Order Canceled by Batch Cancel Process'), false)->save();
        } else {
            //let's save some cancel totals to the order.
            $order
                ->setSubtotalCanceled($subtotalCanceled)
                ->setBaseSubtotalCanceled($baseSubtotalCanceled)

                ->setTaxCanceled($taxCanceled)
                ->setBaseTaxCanceled($baseTaxCanceled)

            //TODO: The shipping amounts need to be figured out if flat rate shipping is ever scrapped.
            // ->setShippingCanceled($this->getShippingAmount() - $this->getShippingInvoiced());
            // ->setBaseShippingCanceled($this->getBaseShippingAmount() - $this->getBaseShippingInvoiced());

                ->setDiscountCanceled($discountCanceled)
                ->setBaseDiscountCanceled($baseDiscountCanceled)

                ->setTotalCanceled($totalCanceled)
                ->setBaseTotalCanceled($baseTotalCanceled)
            ;
            //If the order involves store credit, reward points, or discounts
            //we need to put the order into the review status for manual intervention
            if($order->getDiscountAmount() > 0 || $order->getDiscountCanceled() > 0) {
                $order->addStatusToHistory(Totsy_Sales_Model_Order::STATUS_BATCH_CANCEL_CSR_REVIEW
                    ,Mage::helper('core')->__('Order contains Discounts and requires CSR Review.'
                    .' Please review and move to processing when corrected.'));
            }
            if($order->getRewardCurrencyAmount() > 0 || $order->getRewardPointsBalance() > 0) {
                $order->addStatusToHistory(Totsy_Sales_Model_Order::STATUS_BATCH_CANCEL_CSR_REVIEW
                    ,Mage::helper('core')->__('Order contains Reward Points and requires CSR Review.'
                        .' Please review and move to processing when corrected.'));
            }
            if($order->getCustomerBalanceAmount() > 0) {
                $order->addStatusToHistory(Totsy_Sales_Model_Order::STATUS_BATCH_CANCEL_CSR_REVIEW
                    ,Mage::helper('core')->__('Order contains Store Credit and requires CSR Review.'
                        .' Please review and move to processing when corrected.'));
            }
        }
        $order->save();

        return true;
    }

    /**
     * clean fulfilled ItemQueue
     */
    public function purgeFulfilledStock() {
        //get all cancelled and closed itemQueue Collection
        $itemQueueCollection = Mage::getModel('fulfillmentfactory/itemqueue')->getCollection()
                                ->addFieldToFilter('status', array('in' => array(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CLOSED,
                                                            Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CANCELLED))
                                );
        
        foreach($itemQueueCollection as $itemQueue) {
            //remove this item queue
            $itemQueue->delete();
        }
    }
}
