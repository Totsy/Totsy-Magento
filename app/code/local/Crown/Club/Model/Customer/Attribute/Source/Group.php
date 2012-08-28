<?php
class Crown_Club_Model_Customer_Attribute_Source_Group extends Mage_Customer_Model_Customer_Attribute_Source_Group {
	
	/**
	 * Available customer group options for the Club
	 * @var array
	 */
	protected $_availableOptions = array();
	
	/**
	 * Customer groups source for admin page
	 * @since 0.1.0
	 * @return array
	 */
	public function toOptionArray() {
		
		$_options = array(
			-1 => 'Please Select A Group'
		);
		
		$this->_availableOptions = $_options + $this->getAllOptions();
		return $this->_availableOptions;
	}
}