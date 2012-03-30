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
class Harapartners_FulfillmentFactory_Helper_Data extends Mage_Core_Helper_Abstract{	
	/**
	 * get 2-letter code for states
	 *
	 * @param string $stateName
	 * @param string $countryCode
	 * @return string state code
	 */
	public function getStateCodeByFullName($stateName, $countryCode) {
		$stateCode = $stateName;
		
		$stateObj = Mage::getModel('directory/region')->loadByName($stateName, $countryCode);
		
		if(!empty($stateObj)) {
			$stateCode = $stateObj->getCode();
		}
		
		return $stateCode;
	}
	
	/**
	 * Auxiliary function for pushing order into array, which can avoid duplicate of orders.
	 *
	 * @param Array $orderArray
	 * @param Object $order
	 */
	public function _pushUniqueOrderIntoArray(&$orderArray, $order) {
		if(empty($order)) {
			return;
		}
		
		$shouldBeAdded = true;
		
		foreach($orderArray as $existOrder) {
			if($existOrder->getId() == $order->getId()) {
				$shouldBeAdded = false;
				break;
			}
		}
		
		if($shouldBeAdded) {
			$orderArray[] = $order;
		}
	}
}