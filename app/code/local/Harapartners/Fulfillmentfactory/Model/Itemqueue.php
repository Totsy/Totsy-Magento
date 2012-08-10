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
class Harapartners_Fulfillmentfactory_Model_Itemqueue
    extends Mage_Core_Model_Abstract
{
    const STATUS_PENDING = 1;        // start from pending
    const STATUS_PARTIAL = 2;        // when item is partially filled
    const STATUS_READY   = 3;        // when item is completely filled

    // For item in 'processing' should be 'locked', i.e. do not modify while it's still in processing
    const STATUS_PROCESSING = 4;    // when all items in the related order are filled and order is being processed (capture payment)
    const STATUS_SUSPENDED  = 5;    // when order is held
    const STATUS_SUBMITTED  = 6;    // when order has been submitted
    const STATUS_CLOSED     = 7;    // when order is closed
    const STATUS_CANCELLED  = 8;    // when order has been cancelled

    protected function _construct()
    {
        $this->_init('fulfillmentfactory/itemqueue');
    }

    //Note method will throw exceptions
    public function importDataWithValidation($data)
    {
        //Type casting
        if(is_array($data)){
            $data =  new Varien_Object($data);
        }
        if(!($data instanceof Varien_Object)){
            throw new Exception('Invalid type for data importing, Array or Varien_Object needed.');
        }
        
        $this->addData($data->getData());
        
        if(!$this->getData('store_id')){
            $this->setData('store_id', Mage_Core_Model_App::ADMIN_STORE_ID);
        }
    
        $this->validate();
        return $this;
    }

    public function validate()
    {
        if(!$this->getData('order_item_id') || 
           !$this->getData('order_id') || 
           !$this->getData('store_id') || 
           !$this->getData('product_id')) {
               throw new Exception('Required field is missing!');
        }

        $qtyOrdered = $this->getData('qty_ordered');
        $fulfillCount = $this->getData('fulfill_count');
        $status = $this->getData('status');
        
        if(($qtyOrdered >= $fulfillCount) && ($fulfillCount >= 0)){
            if(($qtyOrdered != $fulfillCount) && ($status == self::STATUS_READY)) {
                throw new Exception('Fulfill count is not matching with status!');
            }
        }else{
            throw new Exception('Invalid fulfill count for item queue object!');
        }
        
        return $this;
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        //Timezone manipulation ignored. Use Magento default timezone (UTC)
        $datetime = date('Y-m-d H:i:s');
        if(!$this->getId()){
            $this->setData('created_at', $datetime);
        }
        $this->setData('updated_at', $datetime);
        
        $this->validate(); //Errors will be thrown as exceptions
        
        parent::_beforeSave();
    }

    /**
     * Execute order submission if all sibling order items are in the READY
     * state.
     *
     * @return Mage_Core_Model_Abstract|void
     */
    protected function _afterSave()
    {
        // locate all order items (those that belong to the same order)
        // including itself
        $orderItems = Mage::getModel($this->_resourceName)->getCollection();
        $orderItems->addFieldToFilter('order_id', $this->getOrderId());

        // inspect each sibling item's status
        $orderReady = true;
        foreach ($orderItems as $item) {
            if (self::STATUS_READY !== intval($item->getStatus())) {
                $orderReady = false;
            }
        }

        // submit this order for fulfillment if all items were READY
        if ($orderReady) {
            $order = Mage::getModel('sales/order')->load($this->getOrderId());
            $orderArray = array($order);

            Mage::getSingleton('fulfillmentfactory/service_dotcom')
                ->submitOrdersToFulfill($orderArray, true);
        }

        return parent::_afterSave();
    }

    /**
     * Load item queue object by order item id
     *
     * @param int $orderItemId
     * @return item queue object
     */
    public function loadByItemId($orderItemId){
        $this->addData($this->getResource()->loadByOrderItemId($orderItemId));
        return $this;
    }
}
