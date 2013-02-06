<?php

class Crown_Vouchers_Model_Mysql4_Association extends Mage_Core_Model_Mysql4_Abstract {
	
	public function _construct() {
		$this->_init('vouchers/association', 'id');
	}
}