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
}