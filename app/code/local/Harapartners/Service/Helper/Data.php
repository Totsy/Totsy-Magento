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
 
class Harapartners_Service_Helper_Data extends Mage_Core_Helper_Url{
    
    const TOTSY_STORE_ID                     = 1;
    const MAMASOURCE_STORE_ID                 = 3;
    const TOTSY_MOBILE_STORE_ID             = 4;
    
    const TOTSY_CUSTOMER_GROUP_ID             = 1;
    const MAMASOURCE_CUSTOMER_GROUP_ID         = 2;
    const DEACTIVATED_USER_GROUP_ID             = 4;
    //another version of translate,which is used in sailthru tags and emails, is in Mage_Catalog.csv
    public static function getNavAgeTranlateArray(){
        return array( 
                        '0_6m' => 'Newborn 0-6M',
                        '6_24m' => 'Infant 6-24M',
                        '1_3y' => 'Toddler 1-3 Y',
                        '3_4y' => 'Preschool 3-4Y',
                        '5_up' => 'School Age 5+',
                        'adult' => 'Adult'
                    );
    }
    
    public function validateStoreByCustomer($customer){
        $correctStoreId = $this->getCorrectStoreId($customer);
        
        if(Mage::app()->getStore()->getId() != $correctStoreId){
            //Redirect
            $urlObject = Mage::getModel('core/url')->setStore($correctStoreId);
            $url = $urlObject->getRouteUrl('customer/account/login');
            Mage::app()->getCookie()->set('store', md5($correctStoreId));
            Mage::getSingleton('customer/session')->setBeforeAuthUrl($url);
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__("This account already exists on ". Mage::app()->getStore()->getName() .".com - please log in below..."),
                Harapartners_Service_Model_Rewrite_Customer_Customer::EXCEPTION_INVALID_STORE_ACCOUNT
            );
        }
        return true;
    }
    
    public function getCorrectStoreId($customer) {
    
        switch($customer->getGroupId()){
            case self::TOTSY_CUSTOMER_GROUP_ID:
                if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/iPhone|Android|BlackBerry/', $_SERVER['HTTP_USER_AGENT'])) {
                    $correctStoreId = self::TOTSY_MOBILE_STORE_ID;
                }else{
                    $correctStoreId = self::TOTSY_STORE_ID;
                }
                break;
            case self::MAMASOURCE_CUSTOMER_GROUP_ID;
                $correctStoreId = self::MAMASOURCE_STORE_ID;
                break;
            default:
                $correctStoreId = self::TOTSY_STORE_ID;
        }
        
        return $correctStoreId;
    }
    
    public function getStoreIdByCustomerGroupId($customerGroupId){
        
    }
    
    public function isTotsyStore(){
        return Mage::app()->getStore()->getId() == self::TOTSY_STORE_ID;
    }    
    
    public function isTotsyCustomer($customer = null){
        if(!!$customer && !!$customer->getId()){
            if($customer->getGroupId() == self::TOTSY_CUSTOMER_GROUP_ID){
                return true;
            }elseif( $customer->getStoreId() == self::TOTSY_STORE_ID || $customer->getStoreId() == self::TOTSY_MOBILE_STORE_ID ){
                return true;
            }
        }
        return false;
    }
    
    public function isMamasourceStore(){
        return Mage::app()->getStore()->getId() == self::MAMASOURCE_STORE_ID;
    }
    
    public function isMamasourceCustomer($customer = null){
        if(!!$customer && !!$customer->getId()){
            if($customer->getGroupId() == self::MAMASOURCE_CUSTOMER_GROUP_ID){
                return true;
            }elseif($customer->getStoreId() == self::MAMASOURCE_STORE_ID){
                return true;
            }
        }
        return false;
    }
    
    public function getDeactivatedId(){
          $groupId = Mage::getModel('customer/group')->getCollection()
          		->addFieldToFilter('customer_group_code', 'deactivated')
          		->getFirstItem()->getCustomerGroupId();
          return $groupId;
    }
    
    public function getServerTime(){
	    $defaultTimezone = date_default_timezone_get();
	    $mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
	    date_default_timezone_set($mageTimezone);
	    $serverTime = now();
	    date_default_timezone_set($defaultTimezone);
	    return strtotime($serverTime)*1000;
    }
}
