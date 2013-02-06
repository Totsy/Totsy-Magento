<?php

class Crown_Vouchers_Model_Mysql4_Association_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	
	protected function _construct() {
		parent::_construct ();
		$this->_init ( 'vouchers/association', 'id' );
	}
	
	public function addCustomerFilter($customer_id) {
		
		$where = ' customer_id = ' . $customer_id;
		
		$this->getSelect()->where($where);
		
		return $this;
	}
	
	public function addProductFilter($product_id) {
		
		$where = ' product_id = ' . $product_id;
		
		$this->getSelect()->where($where);
		
		return $this;
	}
}