<?php
class Harapartners_Paymentfactory_Model_Profile extends Mage_Core_Model_Abstract {
    
    protected function _construct(){
        $this->_init('paymentfactory/profile');
    }
       
    public function loadByCustomerId($customerId){
        // only for FE my account page
        //use resource model load, NOT collection load        
        return $this->getCollection()->loadByCustomerId($customerId);
    }
    
    public function loadByCcNumberWithId($ccNumberWithId){
        $ccNumberWithId = preg_replace('/[\s\-]/', '', $ccNumberWithId);
        return $this->load(md5($ccNumberWithId),'cc_number_hash');
    }
    
    public function getEncryptedSubscriptionId(){
        if(!!$this->getSubscriptionId()){
            try{
                return base64_encode(Mage::getModel('core/encryption')->encrypt($this->getSubscriptionId()));
            }catch (Exception $e){
            }
        }
        return '';
    }
    
    public function loadByEncryptedSubscriptionId($encryptedSubscriptionId){
        if(!!$encryptedSubscriptionId){
            try{
                $subscriptionId = Mage::getModel('core/encryption')->decrypt(base64_decode($encryptedSubscriptionId));
                $this->load($subscriptionId,'subscription_id');
            }catch (Exception $e){
            }
        }
        return $this;
    }

    public function deleteById($ruleId){
        return $this->getResource()->deleteById($ruleId);
    }
    
    public function importDataWithValidation($dataObject){
        
        if(is_array($dataObject)){
            $dataObject = new Varien_Object($dataObject);
        }
        
        if(!$dataObject || !($dataObject instanceof Varien_Object)){
            throw new Exception('Invalid data.');
        }
        
        if(!!$dataObject->getData('customer_id')){
                $this->setData('customer_id', (int)$dataObject->getData('customer_id'));
        }else{
            throw new Exception('Missing Customer Id');
        }
        
        if(!!$dataObject->getData('cybersource_subid')){
             $this->setData('subscription_id',$dataObject->getData('cybersource_subid'));
        }else{
            throw new Exception('Missing Subscription Id');
        }

        if(!!$dataObject->getData('cc_last4')){
             $this->setData('last4no',$dataObject->getData('cc_last4'));
        }else{
            throw new Exception('CC LAST 4');
        }
            
        if(!!$dataObject->getData('cc_type')){
              $this->setData('card_type',$dataObject->getData('cc_type'));
        }else{
            throw new Exception('Missing Card Type');
        }    
           
        if(!!$dataObject->getData('cc_number_hash')){
            $this->setData('cc_number_hash', $dataObject->getData('cc_number_hash'));
        }elseif(!!$dataObject->getData('cc_number')){
            $this->setData('cc_number_hash',md5($dataObject->getCcNumber().$dataObject->getData('customer_id').$dataObject->getData('cc_exp_year').$dataObject->getData('cc_exp_month')));
        }else{
            throw new Exception('Unable to create hash key');
        }       

        if(!!$dataObject->getData('cc_exp_year')){
             $this->setData('expire_year',$dataObject->getData('cc_exp_year'));
        }else{
            throw new Exception('Missing Expire Year');
        }
       
        if(!!$dataObject->getData('cc_exp_month')){
             $this->setData('expire_month',$dataObject->getData('cc_exp_month'));
        }else{
            throw new Exception('Missing Expire Month');
        }
        
        if(!!$dataObject->getData('address_id')){
             $this->setData('address_id',$dataObject->getData('address_id'));
        }else{
            throw new Exception('Missing Billing Address');
        }
        
        if($dataObject->getData('saved_by_customer')){
             $this->setData('saved_by_customer',1);
        }
        return $this;
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