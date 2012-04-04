<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Import_Block_Adminhtml_Import_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {
	
	protected function _prepareForm() {
		$dataObj = new Varien_Object(Mage::registry('import_form_data'));
		$helper = Mage::helper('import');
		
		$form = new Varien_Data_Form();
		$fieldset = $form->addFieldset('import_form', array('legend'=>$helper->__('Import information')));
     
		$fieldset->addField('import_title', 'text', array(
		    'label'     => $helper->__('Title'),
		    'class'     => 'required-entry',
		    'required'  => true,
		    'name'		=> 'import_title',
			'note'		=> 'For internal tracking.'
		));
		
		$category = Mage::getModel('catalog/category')->load($dataObj->getData('category_id'));
		if($category->getId()){
			$fieldset->addField('category_id', 'label', array(
				'label'     => $helper->__('Category/Event ID'),
				'required'  => true,
				'name'		=> 'category_id',
				'note'		=> $helper->__($category->getName())
			));
		}else{
			$fieldset->addField('category_id', 'text', array(
				'label'     => $helper->__('Category/Event ID'),
				'required'  => true,
				'name'		=> 'category_id',
				'note'		=> $helper->__('If specified, the \'category_ids\' field in the import field will be overwritten.')
			));
		}
		
		$fieldset->addField('po_id', 'text', array(
			'label'     => $helper->__('Purchase Order ID'),
			//'required'  => true,
			'name'		=> 'po_id',
			'note'		=> $helper->__('If <b>NOT</b> specified, a new purchase order will be created.')
		));
		
		$fieldset->addField('import_filename', 'file', array(
		    'label'     => $helper->__('File'),
		    'required'  => true,
		    'name'		=> 'import_filename',
		));
		
		$fieldset->addField('action_type', 'select', array(
		    'label'     => $helper->__('Action Type'),
		    'required'  => true,
		    'name'		=> 'action_type',
			'values'    => $helper->getFormActionTypeArray(),
			'note'		=> 'Pending import should be processed manually or by cronjob.'
		));
		
		if ( Mage::registry('import_form_data') ) {
		      $form->setValues(Mage::registry('import_form_data'));
        }

      $form->setValues($dataObj->getData());
      $this->setForm($form);
      return parent::_prepareForm();
	}
}