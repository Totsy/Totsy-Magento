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

class Harapartners_Affiliate_Model_Record extends Mage_Core_Model_Abstract {
    
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;
    
    const TYPE_STANDARD = 1;
    const TYPE_SUPER = 2;
    
    protected function _construct(){
        $this->_init('affiliate/record');
    }
    
    public function loadByAffiliateCode($affiliateCode){
        $this->addData($this->getResource()->loadByAffiliateCode($affiliateCode));
        return $this;
    }

    //Note method will throw exceptions
    public function importDataWithValidation($data)
    {
        //Type casting
        if(is_array($data)){
            $data = new Varien_Object($data);
        }
        if(!($data instanceof Varien_Object)){
            throw new Exception('Invalid type for data importing, Array or Varien_Object needed.');
        }

        // compile tracking codes
        $trackingCodes = array();
        $reqData = $data->getData();
        foreach ($reqData as $key => $value) {
            if (0 === strpos($key, 'trackingcode_')) {
                $idx = substr($key, strpos($key, '_')+1);
                $event = $data["trackingevent_$idx"];
                $code  = $data["trackingcode_$idx"];
                $trackingCodes[$event] = $code;
            }
        }

        $this->setTrackingCode(json_encode($trackingCodes));

        //Forcefully overwrite existing data, certain data may need to be removed before this step
        $this->addData($data->getData());
        
        //Default values should go here
        if(!$this->getData('type')){
            $this->setData('type', self::TYPE_STANDARD);
        }
        if(!$this->getData('status')){
            $this->setData('status', self::STATUS_ENABLED);
        }
        //store_id is defaulted as 0 at the DB level
        
        //Data cleaning
        if(!!($this->getData('sub_affiliate_code'))){
            $rawSubCode = explode(',', trim(trim($this->getData('sub_affiliate_code'), ',')));
            $cleanSubCode = array();
            foreach($rawSubCode as $subCodeValue){
                if(!!trim($subCodeValue)){
                    $cleanSubCode[] = strtolower(trim($subCodeValue));
                }
            }
            $this->setData('sub_affiliate_code', implode(',', $cleanSubCode));
        }        
        
        $this->validate();
        return $this;
    }
    
    public function validate(){
        //Note some of the ID field are validated at the DB level by foreign key
        if(!$this->getData('affiliate_code')){
            throw new Exception('Affilicate code is required!');
        }else{
            if(!preg_match("/^[a-z0-9_]+$/", $this->getData('affiliate_code'))){
                throw new Exception('Affilicate code must be alphanumerical (lowercase) with underscore. Error value: ' . $this->getData('affiliate_code'));
            }
        }
        
        if(!$this->getData('type')){
            throw new Exception('Affilicate type is required!');
        }
        
        if(!!($this->getData('tracking_code'))){
            $result = json_decode($this->getData('tracking_code'), true);
            if(!is_array($result)){
                throw new Exception('Tracking code must be valid JSON');
            }
        }
        
        if(!!($this->getData('sub_affiliate_code'))){
            $cleanSubCode = explode(',', $this->getData('sub_affiliate_code'));
            foreach($cleanSubCode as $subCodeValue){
                if(!preg_match("/^[a-z0-9_]+$/",  $subCodeValue)){
                    throw new Exception('Sub-affilicate code must be alphanumerical (lowercase) with underscore. Error value: ' . $subCodeValue);
                }
            }
        }
        
        return $this;
    }
    
    protected function _beforeSave(){
        parent::_beforeSave();
        
        //For new object which does not specify 'created_at'
        if(!$this->getId() && !$this->getData('created_at')){
            $this->setData('created_at', now());
        }
        
        $this->setData('updated_at', now());
        
        $this->validate(); //Errors will be thrown as exceptions
        return $this;
    }
    
}