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
    public function stockUpdate($availableProducts = array()) {
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
                
                $order = Mage::getModel('sales/order')->load($itemQueue->getOrderId());
                
                Mage::helper('fulfillmentfactory')->_pushUniqueOrderIntoArray($processingOrderCollection, $order);
            }
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
        
        //group item queues by order
        foreach($cancelIdList as $id) {
            $itemQueue = Mage::getModel('fulfillmentfactory/itemqueue')->load($id);
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
            if(!$this->_cancelItemqueue($orderId, $itemQueueList)) {
                $isSuccess = false;
            }
        }
        
        return $isSuccess;
    }
    
    /**
     * cancel/split orders by updated items
     *
     * @param array $updateItemQueueIdList
     * @return bool    indicate if new order has been created
     */
    protected function _cancelItemqueue($orderId, $updateItemQueueIdList) {
        $oldOrder = Mage::getModel('sales/order')->load($orderId);
           $oldQuote = Mage::getModel('sales/quote')->setStoreId($oldOrder->getStoreId())->load($oldOrder->getQuoteId());
        
        $restItems = array();
        
        $items = $oldQuote->getAllItems();
        
        //add rest of items
           foreach($items as $item) {
               $product = Mage::getModel('catalog/product')->load($item->getProductId());
               
               //ignore configurable product
               if(!empty($product) && $product->getTypeId() == 'simple') {
                   $shouldBeAdded = true;
                   
                   foreach($updateItemQueueIdList as &$itemQueue) {
                       $orderItem = Mage::getModel('sales/order_item')->load($itemQueue->getOrderItemId());
                       
                    if($item->getId() == $orderItem->getQuoteItemId()) {
                           $shouldBeAdded = false;
                           break;
                       }
                   }
                   
                   if($shouldBeAdded) {
                       //add parent product
                       //should always add parent product first
                    $parentItem = $item->getParentItem();
                    if(!empty($parentItem)) {
                        $restItems[] = $parentItem;
                    }
                    
                    $restItems[] = $item;
                   }
               }
          }
          
          //cancel orders with nothing available
          if(empty($restItems)) {
              $oldOrder->cancel()->save();
              return true;
          }
          
          $itemListCollection = array (
              array (
                  'items' => $restItems,
                  'state' => Mage_Sales_Model_Order::STATE_NEW,
                  'type'    => 'dotcom'
              )
          );
        
          Mage::helper('ordersplit')->createSplitOrder($oldOrder, $itemListCollection);
          
          return true;
    }
    
    /**
     * update current orders which could be fulfilled
     * may trigger split order function
     */
    public function updateOrderFulfillStatus($orderCollection=array()) {
        
        //TODO remove testing
        //$orderCollection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('status', 'pending');
        
//        $order = Mage::getModel('sales/order')->load(83);
//        $orderCollection = array($order);

        foreach($orderCollection as $order) {
            if(!$order->getId()) {
                continue;
            }
            
            //get items queue by orders
            $itemQueueList = Mage::getModel('fulfillmentfactory/itemqueue')->getCollection()->loadByOrderId($order->getId());
            
            if(empty($itemQueueList) || (count($itemQueueList) == 0)) {
                continue;
            }
            
            $completeItems = array();
            $incompleteItems = array();
            
            //force to load the root order
            //to avoid problems happen when we are not using root order
            $masterOrder = $order;
            $masterOrderId = $masterOrder->getOriginalIncrementId();
            while(!empty($masterOrderId)) {
                $masterOrder = Mage::getModel('sales/order')->loadByIncrementId($masterOrderId);
                $masterOrderId = $masterOrder->getOriginalIncrementId();
            }
            
            $oldQuote = Mage::getModel('sales/quote')->setStoreId($masterOrder->getStoreId())->load($masterOrder->getQuoteId());
            $quoteItems = $oldQuote->getAllItems();
            
            foreach ($itemQueueList as $itemqueue) {
                $quoteItem = null;
                
                //NOTICE: link with correct item id
                foreach($quoteItems as $aQuoteItem) {
                    if($aQuoteItem->getId() == $itemqueue->getOriginalQuoteItemId()) {
                        $quoteItem = $aQuoteItem;
                        break;
                    }
                }
                
                //we don't load in this way because the parent item will be missing.
                //$quoteItem = Mage::getModel('sales/quote_item')->load($itemqueue->getOriginalQuoteItemId());
                
                if(empty($quoteItem)) {
                    continue;
                }
                
                //get parent item
                //always add parent item before child item
                $parentItem = $quoteItem->getParentItem();
                
                if($itemqueue->getStatus() == Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_READY) {
                    if(!empty($parentItem)) {
                        $completeItems[] = $parentItem;
                    }
                    
                    $completeItems[] = $quoteItem;
                }
                elseif($itemqueue->getStatus() == Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PARTIAL) {
                    $originalQty = $quoteItem->getQty();
                    $originalDiscountAmount = $quoteItem->getData('discount_amount');
                    $originalBaseDiscountAmount = $quoteItem->getData('base_discount_amount');
                    
                    if(!empty($parentItem)) {
                        $originalQty = $parentItem->getQty();
                        $originalDiscountAmount = $parentItem->getData('discount_amount');
                        $originalBaseDiscountAmount = $parentItem->getData('base_discount_amount');
                    }
                    
                    $fulfillQty = $itemqueue->getFulfillCount();
                    $restQty = $itemqueue->getQtyOrdered() - $fulfillQty;
                    
                    //in case of quantity is not matching with status
                    if($restQty <= 0){
                        if(!empty($parentItem)) {
                            $completeItems[] = $parentItem;
                        }
                        $completeItems[] = $quoteItem;
                        continue;
                    }
                    
                    $pendingScale = $restQty / $originalQty;
                    
                    //the original quote is left as 'incomplete'
                    if(!empty($parentItem)) {
                        $parentItem->setData('qty', $restQty);
                        
                        $newDiscountAmount = $originalDiscountAmount * $pendingScale;
                        $newBaseDiscountAmount = $originalBaseDiscountAmount * $pendingScale;
                        $parentItem->setData('discount_amount', $newDiscountAmount);
                        $parentItem->setData('base_discount_amount', $newBaseDiscountAmount);
                        
                        $incompleteItems[] = $parentItem;
                    } else {
                        
                        $newDiscountAmount = $originalDiscountAmount * $pendingScale;
                        $newBaseDiscountAmount = $originalBaseDiscountAmount * $pendingScale;
                        $quoteItem->setData('discount_amount', $newDiscountAmount);
                        $quoteItem->setData('base_discount_amount', $newBaseDiscountAmount);
                        
                        $quoteItem->setData('qty', $restQty);
                    }
                    $incompleteItems[] = $quoteItem;

                    //the fulfilled part is created as a new item
                    //NOTE, in the split order logic, the item ID will be cleared, no need to clear it here!
                    $copyParentItem = null;
                    $fulfilledItem = clone $quoteItem;
                    
                    $fulfillScale = $fulfillQty / $originalQty;
                    
                    if(!empty($parentItem)) {
                        $copyParentItem = clone $parentItem;
                        $copyParentItem->setData('qty', $fulfillQty);
                        
                        $newDiscountAmount = $originalDiscountAmount * $fulfillScale;
                        $newBaseDiscountAmount = $originalBaseDiscountAmount * $fulfillScale;
                        $copyParentItem->setData('discount_amount', $newDiscountAmount);
                        $copyParentItem->setData('base_discount_amount', $newBaseDiscountAmount);
                        
                        $completeItems[] = $copyParentItem;
                        $fulfilledItem->setParentItem($copyParentItem);
                    } else {
                        $fulfilledItem->setData('qty', $fulfillQty);
                        
                        $newDiscountAmount = $originalDiscountAmount * $fulfillScale;
                        $newBaseDiscountAmount = $originalBaseDiscountAmount * $fulfillScale;
                        $fulfilledItem->setData('discount_amount', $newDiscountAmount);
                        $fulfilledItem->setData('base_discount_amount', $newBaseDiscountAmount);
                    }
                    $completeItems[] = $fulfilledItem;
                } 
                else {
                    // ===== all other statuses ===== //
                    if(!empty($parentItem)) {
                        $incompleteItems[] = $parentItem;
                    }
                    $incompleteItems[] = $quoteItem;
                }
            }
            
            if(!empty($completeItems)) {
                if(empty($incompleteItems)) {
                    //$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
                }
                else {
                    //trigger split order
                    $itemListCollection = array (
                          array (
                              'items' => $completeItems,
                              'state' => Mage_Sales_Model_Order::STATE_NEW,
                              'type'    => 'dotcom'
                          ),
                          array (
                              'items' => $incompleteItems,
                              'state' => Mage_Sales_Model_Order::STATE_NEW,
                              'type'    => 'dotcom'
                          )
                      );
                      
                    Mage::helper('ordersplit')->createSplitOrder($order, $itemListCollection);
                }
            }
        }
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
