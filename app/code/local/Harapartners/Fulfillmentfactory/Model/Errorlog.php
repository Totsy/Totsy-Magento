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
class Harapartners_Fulfillmentfactory_Model_Errorlog extends Mage_Core_Model_Abstract {
    
    protected function _construct(){
        $this->_init('fulfillmentfactory/errorlog');
    }
    
    public function importDataWithValidation($data) {
        //Type casting
        if(is_array($data)){
            $data =  new Varien_Object($data);
        }
        if(!($data instanceof Varien_Object)){
            throw new Exception('Invalid type for data importing, Array or Varien_Object needed.');
        }
        
        $this->addData($data->getData());
        
        if(!$this->getData('store_id')){
            $this->setData('store_id', Mage_Core_Model_App::ADMIN_STORE_ID);
        }
    
        $this->validate();
        return $this;
    }
    
    public function validate(){
        return $this;
    }
    
    protected function _beforeSave() {
        parent::_beforeSave();
        //Timezone manipulation ignored. Use Magento default timezone (UTC)
        $datetime = date('Y-m-d H:i:s');
        if(!$this->getId()){
            $this->setData('created_at', $datetime);
        }
        $this->setData('updated_at', $datetime);
        
        $this->validate(); //Errors will be thrown as exceptions
        
        parent::_beforeSave();
    }
}