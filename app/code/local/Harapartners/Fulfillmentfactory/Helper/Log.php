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
class Harapartners_FulfillmentFactory_Helper_Log extends Mage_Core_Helper_Abstract{	
	
	/**
	 * log message
	 *
	 * @param string $message
	 * @return log file name
	 */
	public function errorLog($message) {
		$logFileName = 'fulfillment_error_' . date('Y_m_d_his') . '.log';
		
		$errorlogModel = Mage::getModel('fulfillmentfactory/errorlog');
		$errorlogModel->setMessage($message);
		$errorlogModel->importDataWithValidation($errorlogModel->getData())->save();
		
		Mage::log($message, null, $logFileName);
		
		return $logFileName;
	}
	
	/**
	 * log message with order id
	 *
	 * @param string $message
	 * @param unknown_type $orderId
	 * @return log file name
	 */
	public function errorLogWithOrder($message, $orderId) {
		$logFileName = 'fulfillment_error_' . date('Y_m_d_his') . '.log';

		$errorlogModel = Mage::getModel('fulfillmentfactory/errorlog');
		$errorlogModel->setOrderId($orderId);
		$errorlogModel->setMessage($message);
		$errorlogModel->importDataWithValidation($errorlogModel->getData())->save();
		
		Mage::log($message, null, $logFileName);
		
		return $logFileName;
	}
}