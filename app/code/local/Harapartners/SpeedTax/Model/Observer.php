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

class Harapartners_SpeedTax_Model_Observer extends Mage_Core_Model_Abstract {
	
	public function saleOrderInvoicePay(Varien_Event_Observer $observer) {
		//$storeId = $observer->getEvent()->getQuote()->getStoreId();
		$storeId = Mage::app ()->getStore ()->getId ();
		$invoice = $observer->getInvoice ();
		//if(Mage::getStoreConfig('tax/speedtax/post_invoice' , $storeId)) {
		try {
			$calculator = Mage::getModel ( 'speedtax/speedtax_calculate' );
			$order = $invoice->getOrder ();
			$invoice->setData ( "increment_id", $order->getData ( "increment_id" ) );
			if ($calculator->addInvoice ( $invoice )) {
				foreach ( $order->getAllItems () as $item ) {
					/*** make line item ***/
					$calculator->addLine ( $item );
				}
				$result = $calculator->invoiceTaxPost ();
			}
		} catch( Exception $e ) {
		}
		//}
	}
	
//	public function salesOrderPlaceEnd(Varien_Event_Observer $observer) {
//		$storeId = Mage::app ()->getStore ()->getId ();
//		$order = $observer->getOrder ();
//		try {
//			$calculator = Mage::getModel ( 'speedtax/speedtax_calculate' );
//			//$invoice = $observer->getInvoice();
//			//$invoice->setData("increment_id", $order->getData("increment_id"));
//			if ($calculator->addOrder ( $order )) {
//				$result = $calculator->invoiceTaxPending ();
//			}
//		} catch( Exception $e ) {
//		}
//	}
	
	public function salesOrderCreditmemoRefund(Varien_Event_Observer $observer) {
		$storeId = Mage::app ()->getStore ()->getId ();
		try {
			$calculator = Mage::getModel ( 'speedtax/speedtax_calculate' );
			//$invoice = $observer->getInvoice();
			$order = $observer->creditmemo->getOrder();
			//$invoice->setData("increment_id", $order->getData("increment_id"));
			if ($calculator->addCreditmemo ( $order )) {
				$result = $calculator->invoiceTaxPending ();
			}
		} catch( Exception $e ) {
		}
	}
}