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
	
	const TOTSY_STORE_ID 					= 1;
	const TOTSY_CUSTOMER_GROUP_ID 			= 1;
	const MAMASOURCE_STORE_ID 				= 3;
	const MAMASOURCE_CUSTOMER_GROUP_ID 		= 2;
	
	public function isTotsyStore(){
		return Mage::app()->getStore()->getId() == self::TOTSY_STORE_ID;
	}
	
	public function isTotsyCustomer($customer = null){
		if(!!$customer && !!$customer->getId()){
			if($customer->getGroupId() == self::TOTSY_CUSTOMER_GROUP_ID){
				return true;
			}elseif($customer->getStoreId() == self::TOTSY_STORE_ID){
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
	
}