<?php

class Crown_Vouchers_Model_Email extends Mage_Core_Model_Abstract {
	
	protected 	$productId, // Product ID
				$product, // Product Object
				$mailer, // Mail Object
				$customer; // Customer Object
	
	public function sendEmail($product_id) {
		$this->productId = $product_id;
		$this->init();
		$this->send();
	}
	
	protected function init() {
		$this->mailer = Mage::getModel('core/email_template');
		$this->customer = Mage::helper('customer')->getCustomer();
		$this->product = Mage::getModel('catalog/product')->load($this->productId);
	}
	
	
	protected function send() {
		$templateId = Mage::getModel('core/email_template')->loadByCode('voucher_code_email_template')->getId();
		Mage::getModel('core/email_template')->sendTransactional(
			$templateId, 
			'voucher', 
			$this->customer->getEmail(), 
			$this->customer->getName(), 
			array(
				'customer' => $this->customer->getName(), 
				'product_name' => $this->product->getName(),
				'voucher_code' => $this->product->getVoucherCode()
			), 
			Mage::app()->getStore()->getId()
		);
        	
	}
	
}