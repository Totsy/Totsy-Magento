<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.0
 */
class Crown_Import_Block_Adminhtml_Import_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {
	
	/**
	 * (non-PHPdoc)
	 * @see Mage_Adminhtml_Block_Widget_Form::_prepareForm()
	 */
	protected function _prepareForm() {
		$helper = Mage::helper ( 'crownimport' );
		$importModel = $helper->getImportModel();
		$form = new Varien_Data_Form ();
		
		$fieldset = $form->addFieldset ( 'import_form', array (
			'legend' => $helper->__ ( 'Import information' ) 
		) );
		
		$fieldset->addField ( 'import_title', 'text', array (
			'label' => $helper->__ ( 'Title' ), 
			'class' => 'required-entry', 
			'required' => true, 
			'name' => 'import_title', 
			'note' => 'Should new Purchase Order be created, the PO name is also given here. Default value is the category/event name.' 
		) );
		
		// Load Category information
		if (! ! $importModel->getData ( 'category_id' )) {
			$category = Mage::getModel('catalog/category')->load($importModel->getCategoryId());
			$importModel->setCategoryName($category->getName());
			$fieldset->addField ( 'category_id', 'hidden', array (
				'label' => $helper->__ ( 'Category/Event ID' ), 
				'required' => true, 
				'name' => 'category_id', 
				'readonly' => true 
			) );
			$fieldset->addField ( 'category_name', 'text', array (
				'label' => $helper->__ ( 'Category/Event' ), 
				'value' => $importModel->getData ( 'category_name' ), 
				'required' => false, 
				'readonly' => true, 
				'note' => '<strong>Read only</strong>: For reference only.' 
			) );
		} else {
			$fieldset->addField ( 'category_id', 'text', array (
				'label' => $helper->__ ( 'Category/Event ID' ), 
				'required' => true, 
				'name' => 'category_id', 
				'note' => $helper->__ ( 'If specified, the \'category_ids\' field in the import field will be overwritten.' ) 
			) );
		}
		
		$fieldset->addField ( 'po_id', 'select', array (
			'label' => $helper->__ ( 'Purchase Order' ), 
			'required' => true, 
			'name' => 'po_id', 
			'values' => Mage::helper ( 'stockhistory' )->getFormPoArrayByCategoryId ( $importModel->getData ( 'category_id' ), Harapartners_Stockhistory_Model_Purchaseorder::STATUS_OPEN ), 
			'note' => $helper->__ ( 'Products within the same event usually belong to the same PO. Be careful when creating a new PO.' ) 
		) );
		
		$fieldset->addField ( 'vendor_code', 'select', array (
			'label' => $helper->__ ( 'Vendor Code' ), 
			'required' => false, 
			'name' => 'vendor_code', 
			'values' => Mage::helper ( 'stockhistory' )->getFormAllVendorsArray (), 
			'note' => $helper->__ ( '<b>Required</b> when creating new PO.' ) 
		) );
		
		$fieldset->addField ( 'import_filename', 'file', array (
			'label' => $helper->__ ( 'File' ), 
			'required' => true, 'name' => 
			'import_filename', 
			'note' => 'Must be a CSV.' 
		) );
		
		$form->setValues ( $importModel->getData () );
		$this->setForm ( $form );
		return parent::_prepareForm ();
	}

}