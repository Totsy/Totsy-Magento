<?php
class Harapartners_Promotionfactory_Model_Mysql4_Groupcoupon extends Mage_Core_Model_Mysql4_Abstract{
    
    protected function _construct(){
        $this->_init('promotionfactory/groupcoupon', 'entity_id');
    }
    
    public function ruleIdExist($ruleId){
        $select = $this->_getReadAdapter()
                ->select()
                ->distinct()
                ->from($this->getMainTable())
                ->where('rule_id=:rule_id');

        $result = $this->_getReadAdapter()->fetchRow($select, array('rule_id'=>$ruleId));

        if ($result) {
            return true;
        }
        return false;
    }
    
    public function getTotalCodeCount($ruleId){
        $select = $this->_getReadAdapter()
                ->select()
                ->distinct()
                ->from($this->getMainTable(), 'COUNT(*)')
                ->where('rule_id=:rule_id');

        $result = $this->_getReadAdapter()->fetchRow($select, array('rule_id'=>$ruleId));
        return (int) $result;
    }
    
      public function deleteByRuleId($ruleId){
          $coreResource = Mage::getSingleton('core/resource') ;
        $write = $coreResource->getConnection('core_write');
        $query = 'DELETE FROM `promotionfactory_groupcoupon` where `rule_id` = '.$ruleId;
        $write->query($query);
        
      }

}