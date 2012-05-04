<?php
class Harapartners_Promotionfactory_Model_Emailcoupon extends Mage_Core_Model_Abstract {
    
    protected function _construct(){
        $this->_init('promotionfactory/emailcoupon');
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
    
    public function ruleIdExist($ruleId){
        return $this->getResource()->ruleIdExist($ruleId);
    }
    
      public function  emailCouponMacthFail($ruleId,$email){
          return $this->getResource()->emailCouponMacthFail($ruleId,$email);
      }
      
    public function loadByEmailCouponWithEmail($couponCode,$customerEmail){
          $this->addData($this->getResource()->loadByEmailCouponWithEmail($couponCode,$customerEmail));
          return $this;
      }
    
    public function deleteByRuleId($ruleId){
        return $this->getResource()->deleteByRuleId($ruleId);
    }
    
    public function emailCouponCount($couponCode,$customerEmail){
        $collection = $this->getCollection();
          $collection->getSelect()->where('code = ?', $couponCode)->where('email = ?', $customerEmail);
          return $collection->getFirstItem();
    }
    
}