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
		$invoice = $observer->getEvent()->getInvoice();
		$calculator = Mage::getModel ( 'speedtax/speedtax_calculate' );
		
		//Jun, Restoring increment_id, why?
//		$order = $invoice->getOrder ();
//		$invoice->setData ( "increment_id", $order->getData ( "increment_id" ) );

		$calculator->addInvoice ( $invoice );
		$calculator->invoiceTaxPost ();
//		
//		if ($calculator->addInvoice ( $invoice )) {
//			foreach ( $order->getAllItems () as $item ) {
//				/*** make line item ***/
//				$calculator->addLine ( $item );
//			}
//			$result = $calculator->invoiceTaxPost ();
//		}
	}
	
	public function salesOrderCreditmemoRefund(Varien_Event_Observer $observer) {
		$creditMemo = $observer->getEvent()->getCreditMemo();
		$calculator = Mage::getModel ( 'speedtax/speedtax_calculate' );
		$order = $observer->creditmemo->getOrder();
		if ($calculator->addCreditmemo ( $order )) {
			$result = $calculator->invoiceTaxPending ();
		}
	}
	
}