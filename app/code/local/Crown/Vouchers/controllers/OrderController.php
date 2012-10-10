<?php

class Crown_Vouchers_OrderController extends Mage_Core_Controller_Front_Action {
	
	/**
	 * AJAX request to save new order
	 */
	public function createAction() {
		$product_id = $this->getRequest()->getParam('id');
		
		$customer_id = Mage::helper('customer')->getCustomer()->getId();
		
		$association = Mage::getModel('vouchers/association');
		
		
		if(!$product_id) {
			$this->returnResult(array('error' => 'Invalid Product ID'));
			return;
		}
		
		if(!$customer_id) {
			$this->returnResult(array('error' => 'Please login first. '));
			return;
		}
		
		if(Mage::helper('vouchers')->hasAssociation($customer_id, $product_id)) {
			$this->returnResult(array('error' => 'You have already received this voucher. '));
			return;
		}
		
		// Create order
		Mage::getModel('vouchers/order')->createOrder($product_id);
		
		// Send Voucher Email
		Mage::getModel('vouchers/email')->sendEmail($product_id);
		
		$this->returnResult(array('success' => 'Your email has been sent!'));
	}
	
	private function returnResult($result) {
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
}