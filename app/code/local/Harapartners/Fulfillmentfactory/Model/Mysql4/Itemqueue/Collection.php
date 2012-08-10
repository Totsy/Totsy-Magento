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
class Harapartners_Fulfillmentfactory_Model_Mysql4_Itemqueue_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract{
    
    public function _construct(){
        $this->_init('fulfillmentfactory/itemqueue');
    }

    /**
     * get Itemqueue collection by order Id
     *
     * @param int $orderId
     * @return Collection
     */
    public function loadByOrderId($orderId) {
        $this->getSelect()
            ->where('order_id=' . $orderId);

        return $this;
    }

    /**
     * get Itemqueue collection by order Id
     *
     * @param int $orderId
     * @return Collection
     */
    public function loadByOrderItemId($orderItemId) {
        $this->getSelect()
            ->where('order_item_id=' . $orderItemId);

        return $this;
    }
    
    /**
     * Load item queues which could be submitted to DOTcom
     *
     * @return Collection
     */
    public function loadReadyForSubmitItemQueue() {
        //for now, it is using Processing to indicate this item could be submitted.
        $collection = $this->addFieldToFilter('status', Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_READY);
        
        return $collection;
    }
    
    
    /**
     * get unprocessed (pending and partial) ItemQueue collection of this product, based on $aProduct['sku']
     * order by date ASC
     * limit 0, qty
     * @param string $productSku
     * @param int $limit
     * @return Collection
     */
    public function loadIncompleteItemQueueByProductSku($productSku)
    {
        $collection = $this->addFieldToFilter('sku', $productSku)
            ->addFieldToFilter('status', array('in' => array(
                Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PENDING,
                Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PARTIAL
            )))
            ->setOrder('created_at');

        return $collection;
    }
}
