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
            'name'        => 'import_title',
            'note'        => 'Should new Purchase Order be created, the PO name is also given here. Default value is the category/event name.'
        ));
        
        $fieldset->addField('po_id', 'select', array(
                'label'     => $helper->__('Purchase Order'),
                'required'  => true,
                'name'        => 'po_id',
                'values'    => Mage::helper('stockhistory')->getFormPoArrayByCategoryId(
                        $dataObj->getData('category_id'), 
                        Harapartners_Stockhistory_Model_Purchaseorder::STATUS_OPEN
                ),
                'note'        => $helper->__('Products within the same event usually belong to the same PO. Be careful when creating a new PO.')
        ));
        
        $fieldset->addField('vendor_code', 'select', array(
                'label'     => $helper->__('Vendor Code'),
                'required'  => false,
                'name'        => 'vendor_code',
                'values'    => Mage::helper('stockhistory')->getFormAllVendorsArray(),
                'note'        => $helper->__('<b>Required</b> when creating new PO.')
        ));
        
        if(!!$dataObj->getData('category_id')){
            $fieldset->addField('category_id', 'text', array(
                'label'     => $helper->__('Category/Event ID'),
                'required'  => true,
                'name'        => 'category_id',
                'readonly'    => true,
                'note'        => $helper->__('Target category/event name: "<b>' . $dataObj->getData('category_name') . '</b>". Read Only. Due to potential name conflict, ID is required.')
            ));
        }else{
            $fieldset->addField('category_id', 'text', array(
                'label'     => $helper->__('Category/Event ID'),
                'required'  => true,
                'name'        => 'category_id',
                'note'        => $helper->__('If specified, the \'category_ids\' field in the import field will be overwritten.')
            ));
        }
        
        $fieldset->addField('import_filename', 'file', array(
            'label'     => $helper->__('File'),
            'required'  => true,
            'name'        => 'import_filename',
        ));
        
        $fieldset->addField('action_type', 'select', array(
            'label'     => $helper->__('Action Type'),
            'required'  => true,
            'name'        => 'action_type',
            'values'    => $helper->getFormActionTypeArray(),
            'note'        => 'Large imports (150+ lines) will take a long time to run and index, please cut them into smaller pieces..'
        ));
        
        if ( Mage::registry('import_form_data') ) {
              $form->setValues(Mage::registry('import_form_data'));
        }

      $form->setValues($dataObj->getData());
      $this->setForm($form);
      return parent::_prepareForm();
    }
    
}