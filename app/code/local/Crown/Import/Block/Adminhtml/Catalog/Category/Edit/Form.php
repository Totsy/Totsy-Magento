<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Block_Adminhtml_Catalog_Category_Edit_Form extends Harapartners_Service_Block_Rewrite_Adminhtml_Catalog_Category_Edit_Form {
	/**
	 * (non-PHPdoc)
	 * @see Harapartners_Service_Block_Rewrite_Adminhtml_Catalog_Category_Edit_Form::_prepareLayout()
	 */
	protected function _prepareLayout() {
		parent::_prepareLayout ();
        $categoryId = (int) $this->getCategory()->getId();
		if (! in_array ( $categoryId, $this->getRootIds () )) {
			
			if ($this->_isAllowedAction ( 'import' )) {
				$this->setChild ( 'crownimport_product_button', $this->getLayout ()->createBlock ( 'adminhtml/widget_button' )->setData ( array (
					'label' => Mage::helper ( 'catalog' )->__ ( 'Import Products Rapid' ), 
					'onclick' => "setLocation('" . $this->getUrl ( 'crownimport/adminhtml_import/newByCategory', array ('category_id' => $categoryId ) ) . "')", 
					'class' => 'add' 
				)));
			}
		}
		return $this;
	}
	
	/**
	 * Gets the HTML for the button
	 * @since 1.0.0
	 * @return string
	 */
	public function getCrownimportProductButtonHtml() {
		if ($this->hasStoreRootCategory ()) {
			return $this->getChildHtml ( 'crownimport_product_button' );
		}
		return '';
	}
}