<?php

class Harapartners_Import_Block_Adminhtml_Import_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('import_form', array('legend'=>Mage::helper('import')->__('Import information')));
     
      $fieldset->addField('import_title', 'text', array(
          'label'     => Mage::helper('import')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'import_title',
      ));

      $fieldset->addField('category_id', 'text', array(
			'label'     => Mage::helper('import')->__('Category/Event ID'),
			//'required'  => true,
			'name'      => 'category_id',
      		'note'		=> Mage::helper('import')->__('If specified, the \'category_ids\' field in the import field will be overwritten.')
      ));
      
      $fieldset->addField('po_id', 'text', array(
			'label'     => Mage::helper('import')->__('Purchase Order ID'),
			//'required'  => true,
			'name'      => 'po_id',
      		'note'		=> Mage::helper('import')->__('If NOT specified, a new purchase order will be created.')
      ));
      
      $fieldset->addField('import_filename', 'file', array(
          'label'     => Mage::helper('import')->__('File'),
      	  'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'import_filename',
	  ));
		
     
      if ( Mage::getSingleton('adminhtml/session')->getHpImportFormData() ) {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getHpImportFormData());
          Mage::getSingleton('adminhtml/session')->setHpImportFormData(null);
      } elseif ( Mage::registry('import_data') ) {
          $form->setValues(Mage::registry('import_data')->getData());
      }
      return parent::_prepareForm();
  }
}