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


class Harapartners_Customertracking_Model_Record extends Mage_Core_Model_Abstract {
	
	const STATUS_NEW = 1;
	const STATUS_EMAIL_CONFIRMED = 2;
	const STATUS_EMAIL_SOFTBRONCE = 3;
	const STATUS_EMAIL_HARDBRONCE = 4;
	const STATUS_EMAIL_OTHER_PROBLEMS = 5;
	
	const MAX_LEVEL_ALLOWED = 1; //level begins with 0
    
    protected function _construct(){
        $this->_init('customertracking/record');
    }
    
	public function loadByCustomerId($customerId){
	    $this->addData($this->getResource()->loadByCustomerId($customerId));
    	return $this;
    } 

    public function loadByCustomerEmail($customerEmail){
	    $this->addData($this->getResource()->loadByCustomerEmail($customerEmail));
    	return $this;
    }
    
//Note method will throw exceptions
    public function importDataWithValidation($data){
    	
    	//Type casting
    	if(is_array($data)){
    		$data =  new Varien_Object($data);
    	}
    	if(!($data instanceof Varien_Object)){
    		throw new Exception('Invalid type for data importing, Array or Varien_Object needed.');
    	}
    	
    	//Forcefully overwrite existing data, certain data may need to be removed before this step
    	$this->addData($data->getData());
    	
    	if(!$this->getData('status')){
    		$this->setData('status', self::STATUS_NEW);
    	}
    	//store_id is defaulted as 0 at the DB level
    	
		$this->validate();
		return $this;
    }
    
    public function validate(){
    	//Note some of the ID field are validated at the DB level by foreign key
    	return $this;
    }
    
    protected function _beforeSave(){
    	parent::_beforeSave();
    	//For new object which does not specify 'created_at'
    	if(!$this->getId() && !$this->getData('created_at')){
    		$this->setData('created_at', now());
    	}
    	//Always specify 'updated_at'
    	$this->setData('updated_at', now());
    	$this->validate(); //Errors will be thrown as exceptions
    	return $this;
    }
}
