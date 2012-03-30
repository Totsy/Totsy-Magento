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

class Harapartners_MobileApi_Model_Customer_Session extends Mage_Customer_Model_Session
{
	public function logout(){
		
		if ($this->isLoggedIn()) {
            Mage::dispatchEvent('customer_logout', array('customer' => $this->getCustomer()) );
            $this->setId(null);
            $this->getCookie()->delete($this->getSessionName());
            return true;
        }
        return false;
	}
}