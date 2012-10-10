<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Block_Adminhtml_Profile_Status extends Mage_Adminhtml_Block_Template {
	/**
	 * (non-PHPdoc)
	 * @see Mage_Core_Block_Template::_construct()
	 */
	protected function _construct() {
		parent::_construct ();
		$this->setTemplate ( 'crown/import/status.phtml' );
	}
}