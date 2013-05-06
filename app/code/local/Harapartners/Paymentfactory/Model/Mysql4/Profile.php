<?php
class Harapartners_Paymentfactory_Model_Mysql4_Profile extends Mage_Core_Model_Mysql4_Abstract{

    protected function _construct(){
        $this->_init('paymentfactory/profile', 'entity_id');
    }

    public function deleteById($ruleId){
        $write = $this->_getWriteAdapter();
        $write->delete($this->getMainTable(), $write->quoteInto('`entity_id` IN(?)', $ruleId));
        return $this;
      }

    public function getLastSubscriptionId($customerId)
    {
        $adapter = $this->_getReadAdapter();
        $orderSelect = $adapter->select()
            ->from($this->getTable('sales/order'), array('entity_id'))
            ->where('customer_id = :customer_id')
            ->order('created_at ' . Zend_Db_Select::SQL_DESC)
            ->limit(1);

        $orderId = $adapter->fetchOne($orderSelect, array('customer_id' => $customerId));

        $paymentSelect = $adapter->select()
            ->from($this->getTable('sales/order_payment'), array('cybersource_subid'))
            ->where('parent_id = :order_id')
            ->limit(1);

        return $adapter->fetchOne($paymentSelect, array('order_id' => $orderId));
    }
}