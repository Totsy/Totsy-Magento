<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Widget_Grid_Container {
	
	/**
	 * 
	 * Enter description here ...
	 */
	public function __construct() {
		$this->_controller = 'adminhtml_import';
		$this->_blockGroup = 'crownimport';
		$this->_headerText = Mage::helper ( 'crownimport' )->__ ( 'Import Manager' );
		$this->_addButtonLabel = Mage::helper ( 'crownimport' )->__ ( 'Add Import' );
		parent::__construct ();
	}
}