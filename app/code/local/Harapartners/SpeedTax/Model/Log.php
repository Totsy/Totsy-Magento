<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license [^]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 *
 */
class Harapartners_SpeedTax_Model_Log extends Mage_Core_Model_Abstract {
	
	public function log($log) {
		
		if ($log ["error"] == true) {
			//error log
			$logModel = Mage::getModel ( 'speedtax/log_error' );
			$logModel->setData ( 'event', $log ['event'] );
			$logModel->setData ( 'result_type', $log ['result_type'] );
			$logModel->setData ( 'message', $log ['message'] );
			$logModel->setData ( 'address_shipping_from', $log ['address_shipping_from'] );
			$logModel->setData ( 'address_shipping_to', $log ['address_shipping_to'] );
			$logModel->setData ( 'customer_name', $this ['customer_name'] );
			$logModel->save ();
		}
		if ($log ["call"] == true) {
			
			/*$log = array ();
			$log ['event'] = "testA";
			$log ['result_type'] = "testA";
			$log ['invoice_num'] = "testA";
			$log ['gross'] = "testA";
			$log ['exempt'] = null;
			$log ['tax'] = "testA";
			$log ["call"] = true;
			
			$logModel = Mage::getModel ( 'speedtax/log_call' );
			$logModel->setData ( 'event', $log ['event'] );
			$logModel->setData ( 'result_type', $log ['result_type'] );
			$logModel->setData ( 'invoice_num', $log ['invoice_num'] );
			$logModel->setData ( 'gross', $log ['gross'] );
			$logModel->setData ( 'exempt', $log ['exempt'] );
			$logModel->setData ( 'tax', $log ['tax'] );
			$logModel->save ();
			return;*/
			
			//call log
			$logModel = Mage::getModel ( 'speedtax/log_call' );
			$logModel->setData ( 'event', $log ['event'] );
			$logModel->setData ( 'result_type', $log ['result_type'] );
			$logModel->setData ( 'invoice_num', $log ['invoice_num'] );
			$logModel->setData ( 'gross', $log['gross']);
			$logModel->setData ( 'exempt', $log ['exempt'] );
			$logModel->setData ( 'tax', $log['tax']);
			$logModel->save ();
		}
	}
}
