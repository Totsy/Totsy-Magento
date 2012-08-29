<?php
class Crown_CustomerIndex_Model_Mysql4_CustomerIndex_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Core_Model_Resource_Db_Collection_Abstract::_construct()
	 */
	public function _construct() {
		$this->_init('CustomerIndex/CustomerIndex');
	}
}