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
		$config = Mage::getModel('core/config');
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
    	$quote = parent::getQuote();
    	if(!Mage::registry('has_expire_cart_by_rushcheckout')){
    		$checkoutSession = Mage::getSingleton('checkout/session');
    		$countdown = $checkoutSession->getCountDownTimer();
        	$timeout = $checkoutSession->getQuoteItemExpireTime();
    	    if($this->_getCurrentTime() - $countdown > $timeout){
  	        	foreach($quote->getAllItems() as $item){
					$item->delete();			
				}
	        }
    		Mage::register('has_expire_cart_by_rushcheckout', true);
    	}
    	$this->_quote = $quote;
        return $this->_quote;
    }

    public function getQuoteItemExpireTime(){	
    	return $this->_getTimerconfig();	
    }
    
    /**
     *  Run Crob to clean the  timeout cart
     */
    public function cartCleanCron(){
    	$cart = Mage::getSingleton('checkout/cart');
    	$this->cartTimeoutCheck($cart);
    }
    
    public function cartTimeoutCheck($cart){
        $countdown = Mage::getSingleton('checkout/session')->getCountDownTimer();
        $timeout = $this->getQuoteItemExpireTime();
        if($this->_getCurrentTime() - $countdown > $timeout){
        	$cart->getQuote();
        	$cart->truncate();
        }
    }

}