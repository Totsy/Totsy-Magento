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
            $customer_id = $order->getCustomerId();
			foreach($items as $item) {
				$productIds[] = $item->getProductId();
            }

            $productCollection = Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect('one_time_purchase')
                ->addFieldToFilter('entity_id', array('in'=>$productIds));

            foreach ($productCollection as $product) {
				$product_id = $product->getId();
				if($product->hasOneTimePurchase() && $product->getOneTimePurchase()) {
					if(!Mage::helper('vouchers')->hasAssociation($customer_id, $product_id)) {
						Mage::helper('vouchers')->saveAssociation($customer_id, $product_id);
					}
				}
			}
		}
	}
	
}