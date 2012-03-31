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

      $fieldset->addField('import_filename', 'file', array(
          'label'     => Mage::helper('import')->__('File'),
      	  'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'import_filename',
	  ));
		
//      $fieldset->addField('status', 'select', array(
//          'label'     => Mage::helper('import')->__('Status'),
//          'name'      => 'status',
//          'values'    => array(
//              array(
//                  'value'     => 1,
//                  'label'     => Mage::helper('import')->__('Enabled'),
//              ),
//
//              array(
//                  'value'     => 2,
//                  'label'     => Mage::helper('import')->__('Disabled'),
//              ),
//          ),
//      ));
     
//      $fieldset->addField('content', 'editor', array(
//          'name'      => 'content',
//          'label'     => Mage::helper('import')->__('Content'),
//          'title'     => Mage::helper('import')->__('Content'),
//          'style'     => 'width:700px; height:500px;',
//          'wysiwyg'   => false,
//          'required'  => true,
//      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getHpImportFormData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getHpImportFormData());
          Mage::getSingleton('adminhtml/session')->setHpImportFormData(null);
      } elseif ( Mage::registry('import_data') ) {
          $form->setValues(Mage::registry('import_data')->getData());
      }
      return parent::_prepareForm();
  }
}