<?php 
class Harapartners_HpCheckout_Helper_Data extends Mage_Core_Helper_Abstract {
    
	//Harapartners, yang, START
    //For cart timer
    public function getCurrentTime(){
        
        $defaultTimezone = date_default_timezone_get();
        $mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);            
        date_default_timezone_set($mageTimezone);
        $timer = now();
        date_default_timezone_set($defaultTimezone);
        
        return strtotime($timer);
    }
    //Harapartners, yang, END
	
}