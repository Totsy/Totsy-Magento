<?php
class Crown_CustomerIndex_Model_Mysql4_CustomerIndex extends Mage_Core_Model_Mysql4_Abstract {
	/**
	 * (non-PHPdoc)
	 * @see Mage_Core_Model_Resource_Abstract::_construct()
	 */
	public function _construct() {
		$this->_init ( 'CustomerIndex/CustomerIndex', 'entity_id' );
	}
}