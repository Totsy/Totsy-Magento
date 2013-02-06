<?php

class Crown_Vouchers_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public function hasAssociation($customer_id, $product_id) {
		$products = Mage::getModel('vouchers/association')
		->getCollection()
		->addCustomerFilter($customer_id)
		->addProductFilter($product_id);
		
		if(count($products) > 0) {
			return true;
		}
		else return false;
	}
	
	/**
	 * Saves the customer product association into the database
	 * 
	 * @param int $customer_id 
	 * @param int $product_id
	 */
	public function saveAssociation($customer_id, $product_id) {
		$vouchers = Mage::getModel('vouchers/association');
		
		$vouchers->setCustomerId($customer_id);
		$vouchers->setProductId($product_id);
		$vouchers->save();
	}

    public function getDiscount($originalPrice, $salePrice) {

        $discount = $originalPrice ? (1.00 - ($salePrice / $originalPrice)) * 100 : $salePrice;
        return substr($discount, 0, 2) . '%';
    }

}