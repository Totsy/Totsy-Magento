<?php

class Crown_Vouchers_Model_Observer extends Mage_Core_Model_Abstract {
	
	/**
	 * Checks for a one time purchase product and saves the association to the customer
	 * 
	 * Triggered on observer: sales_order_save_after
	 * 
	 * @param object $observer
	 */
	public function saveOneTimePurchase(Varien_Event_Observer $observer) {
		/* @var $order Mage_Sales_Model_Order */
		$order = $observer->getEvent()->getOrder();
		
		$items = $order->getItemsCollection('virtual');

		if(count($items) > 0 ) {
			foreach($items as $item) {
				
				$product_id = $item->getProductId();
				
				$customer_id = $order->getCustomerId();
				
				$product = Mage::getModel('catalog/product')->load($product_id);
				
				if($product->hasOneTimePurchase()) {
					
					if(!Mage::helper('vouchers')->hasAssociation($customer_id, $product_id)) {
						Mage::helper('vouchers')->saveAssociation($customer_id, $product_id);
					}
				}
			}
		}
	}
	
}