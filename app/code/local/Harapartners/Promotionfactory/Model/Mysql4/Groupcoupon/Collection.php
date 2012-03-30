<?php
class Harapartners_Promotionfactory_Model_Mysql4_Groupcoupon_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract{
    
	public function _construct(){
        $this->_init('promotionfactory/groupcoupon');
    }
    
    public function getAllIds($limit=null, $offset=null){
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->from(null, 'rule_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }
    
   
}