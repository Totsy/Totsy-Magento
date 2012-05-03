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
class Harapartners_Fulfillmentfactory_Model_Mysql4_Itemqueue extends Mage_Core_Model_Mysql4_Abstract{
    
    protected $_read;
    protected $_write;
    
    protected function _construct(){
        $this->_init('fulfillmentfactory/itemqueue', 'itemqueue_id');
        $this->_read = $this->_getReadAdapter();
        $this->_write = $this->_getWriteAdapter();
    }
    
    /**
     * load itemqueue object by order item id
     *
     * @param int $orderItemId
     * @return Array
     */
    public function loadByOrderItemId($orderItemId){
        $select = $this->_read->select()
                ->from($this->getMainTable())
                ->where('order_item_id=:order_item_id');
        $result = $this->_read->fetchRow($select, array('order_item_id'=>$orderItemId));
        if ($result) {
            return $result;
        }
        return array();
    }
    
}