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
  	
  	public function loadBySubscriptionId($subscriptionId){
  		$adapter = $this->_getReadAdapter();
    	$select = $adapter->select()
            	->from($this->getMainTable())
            	->where('subscription_id =:subscription_id');
       	$data = $adapter->fetchRow($select, array('subscription_id' => $subscriptionId));
		if(!$data){
    		$data = array();
    	}
    	return $data;
  		
  	}
	
    
    public function loadByCcNumberHash($ccNumberHash){
		$adapter = $this->_getReadAdapter();
    	$select = $adapter->select()
            	->from($this->getMainTable())
            	->where('cc_number_hash=:cc_number_hash');
       	$data = $adapter->fetchRow($select, array('cc_number_hash' => $ccNumberHash));
		if(!$data){
    		$data = array();
    	}
    	return $data;
    }
}