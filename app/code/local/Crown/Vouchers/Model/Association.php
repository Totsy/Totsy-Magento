<?php

class Crown_Vouchers_Model_Association extends Mage_Core_Model_Abstract {
	
	public function _construct() {
		$this->_init('vouchers/association');
		parent::_construct();
	}
}