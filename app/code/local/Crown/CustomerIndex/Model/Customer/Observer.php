<?php
class Crown_CustomerIndex_Model_Customer_Observer {
	
	/**
	 * Updates customer flat table with changes.
	 * @param unknown_type $observer
	 */
	public function customerUpdateObserver($observer) {
		Mage::helper('CustomerIndex')->reindexCustomerFlat($observer->getCustomer()->getId());
	}
}