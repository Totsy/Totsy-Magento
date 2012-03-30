<?php
class Harapartners_Recurringmembership_Model_Profile extends Mage_Core_Model_Abstract {
	
    protected function _construct(){
        $this->_init('recurringmembership/profile');
    }
    
	public function loadByEntityId($entityId){
    	$collection = $this->getCollection();
  		$collection->getSelect()->where('entity_id = ?', $entityId);//->limit(1);
    	return $collection->getFirstItem();
    }
 	
	public function loadByCustProductId($customerId,$productId){
    	$collection = Mage::getModel('recurringmembership/profile')->getCollection();
  		$collection->getSelect()->where('cust_id = ?', $customerId)->where('product_id = ?', $productId);//->limit(1);
    	return $collection->getFirstItem();
    }
    
	public function loadByCustomerId($customerId){
    	$collection = Mage::getModel('recurringmembership/profile')->getCollection();
  		$collection->getSelect()->where('cust_id = ?', $customerId);//->limit(1);
    	return $collection->getFirstItem();
    }
    
    //This is for updating 'created_at', 'updated_at' and 'store_id'
    protected function _beforeSave(){
    	//Timezone manipulation ignored. Use Magento default timezone (UTC)
		//$timezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
		$datetime = date('Y-m-d H:i:s');
    	if(!$this->getId()){
    		$this->setData('created_at', $datetime);
    	}
    	$this->setData('updated_at', $datetime);
    	if(!$this->getStoreId()){
    		$this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
    	}
    	parent::_beforeSave();
    }
    
}