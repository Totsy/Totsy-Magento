<?php
class Harapartners_Promotionfactory_Model_Mysql4_Emailcoupon extends Mage_Core_Model_Mysql4_Abstract{
	
    protected function _construct(){
        $this->_init('promotionfactory/emailcoupon', 'entity_id');
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
    
	public function loadByEmailCouponWithEmail($couponCode,$customerEmail){
		$read = $this->_getReadAdapter();
  		$select = $read->select()->from($this->getMainTable())->where('code= ?', $couponCode)->where('email=?',$customerEmail);
  		$result = $read->fetchRow($select);
  		if(!$result){
  			$result = array();
  		}
  		return $result;
	}
    
    
    
	public function  emailCouponMacthFail($ruleId,$email){
		
  		$select = $this->_getReadAdapter()
        		->select()
        		->distinct()
            	->from($this->getMainTable())
            	->where('rule_id=:rule_id')
            	->where('email=:email');
            	
        $result = $this->_getReadAdapter()->fetchRow($select, array('rule_id'=>$ruleId,'email'=>$email));

        if ($result) {
            return false;
        }
        return TRUE;
  	}
    
  	public function deleteByRuleId($ruleId){
  		$coreResource = Mage::getSingleton('core/resource') ;
		$write = $coreResource->getConnection('core_write');
		//get statea
		$query = 'DELETE FROM `promotionfactory_emailcoupon` where `rule_id` = '.$ruleId;
		$write->query($query);
		//return as a flag
  	}
  	
}