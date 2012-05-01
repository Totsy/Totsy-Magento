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

class Harapartners_Service_Model_Rewrite_Checkout_Session extends Mage_Checkout_Model_Session {
	
	protected function _getTimerconfig(){
		$configKey = 'limit_timer';
		$timer = Mage::getStoreConfig('config/rushcheckout_timer/'.$configKey);
		return $timer;
	}
	
    /**
     * Get current local time for timer by HP
     */
    protected function _getCurrentTime(){
   		$defaultTimezone = date_default_timezone_get();
		$mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);			
		date_default_timezone_set($mageTimezone);
		$timer = now();
		date_default_timezone_set($defaultTimezone);
		
		return strtotime($timer);
    }
	
    public function getQuote(){	
    	parent::getQuote();
    	//Only check once for expiration, for non-empty quote
    	if(!Mage::registry('has_expire_cart_by_rushcheckout')){
    		Mage::unregister('has_expire_cart_by_rushcheckout');
    		Mage::register('has_expire_cart_by_rushcheckout', true);
    		$countdown = $this->getCountDownTimer();
        	$timeout = $this->getQuoteItemExpireTime();
        	
    		if(!!count($this->_quote->getAllItems())
    				&& $this->_getCurrentTime() - $countdown > $timeout
    		){
    	    	foreach($this->_quote->getAllItems() as $item){
    	    		$item->isDeleted(true);
					$item->delete();			
				}
				$this->loadCustomerQuote();
	        }
    	}

        return $this->_quote;
    }

    public function getQuoteItemExpireTime(){	
    	return $this->_getTimerconfig();	
    }

}