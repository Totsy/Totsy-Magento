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

class Harapartners_Customertracking_Block_Welcome extends Mage_Core_Block_Template {
	
    public function _toHtml(){
    	$html = '';
    	$cookie = Mage::app()->getCookie();
    	$key = Harapartners_Customertracking_Helper_Data::COOKIE_CUSTOMER_WELCOME;
    	if(!!$cookie->get($key) || true ){
    	    if (!$this->getTemplate()) {
            	return '';
        	}
        	$html = $this->renderView();
        	$cookie->delete($key); //Note cookie still available till next page request
    	}    	
    	return $html;
    }
    
}