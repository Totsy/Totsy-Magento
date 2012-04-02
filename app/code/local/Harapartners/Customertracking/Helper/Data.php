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
 
class Harapartners_Customertracking_Helper_Data extends Mage_Core_Helper_Abstract{
	
	const COOKIE_CUSTOMER_WELCOME = 'CUSTOMER_WELCOME';
	const COOKIE_IS_ACTIVE = '1';
	
	public function getGridStatusArray(){
		return array(
				Harapartners_Customertracking_Model_Record::STATUS_NEW => 'New',
				Harapartners_Customertracking_Model_Record::STATUS_EMAIL_CONFIRMED => 'Email Confirmed'
		);
	}
	
}