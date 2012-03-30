<?php
class Harapartners_Recurringmembership_Model_Mysql4_Profile_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract{
    
	public function _construct(){
        $this->_init('recurringmembership/profile');
    }
    
    public function loadByCustomerId($customerId){
    	if($this->_isCollectionLoaded){
    		throw new Mage_Core_Model_Exception('Cannot reload the collection.');
    	}else{
    		$this->getSelect()->where('`cust_id` = ?', $customerId);
    		$this->load();
    	}
    	return $this;
    }
   
}