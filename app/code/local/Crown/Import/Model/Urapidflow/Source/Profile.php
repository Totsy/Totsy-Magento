<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Model_Urapidflow_Source_Profile extends Mage_Core_Model_Abstract {
	
	/**
	 * Gets a list of the uRapidFlow profiles
	 * @since 1.0.0
	 * @todo Restrict to profiles not being used by import profiles
	 * @return array
	 */
	public function getAllOptions() {
		if (! $this->_options) {
			$_extraOptions = array(
				-1 => 'Please Select a Profile.',
			);
			/* @var $options Unirgy_RapidFlow_Model_Mysql4_Profile_Collection */
			$options = Mage::getModel ( 'urapidflow/profile' )->getCollection ();
			$options->addFilter('profile_type', 'import');
			$options->addFilter('data_type', 'product');
			$optionsArray = $this->_toOptionArray ( $options, 'profile_id', 'title' );
			$this->_options = $_extraOptions + $optionsArray;
		}
		return $this->_options;
	}
	
	/**
	 * Convert items array to array for select options
	 *
	 * return items array
	 * array(
	 * 		$index => array(
	 * 			'value' => mixed
	 * 			'label' => mixed
	 * 		)
	 * )
	 *
	 * @param 	object $collection
	 * @param   string $valueField
	 * @param   string $labelField
	 * @since 	1.0.0
	 * @return  array
	 */
	protected function _toOptionArray($collection, $valueField = 'id', $labelField = 'name', $additional = array()) {
		$res = array ();
		$additional ['value'] = $valueField;
		$additional ['label'] = $labelField;
		
		foreach ( $collection as $item ) {
			foreach ( $additional as $code => $field ) {
				$data [$code] = $item->getData ( $field );
			}
			$res [] = $data;
		}
		return $res;
	}
	
	/**
	 * Customer groups source for admin page
	 * @since 1.0.0
	 * @return array
	 */
	public function toOptionArray() {
		if (is_null ( $this->_availableOptions ))
			$this->_availableOptions = $this->getAllOptions ();
		return $this->_availableOptions;
	}
}