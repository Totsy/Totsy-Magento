<?php
class Crown_CustomerIndex_Model_CustomerIndex extends Mage_Core_Model_Abstract {
	/**
	 * (non-PHPdoc)
	 * @see Varien_Object::_construct()
	 */
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'CustomerIndex/CustomerIndex' );
	}
}