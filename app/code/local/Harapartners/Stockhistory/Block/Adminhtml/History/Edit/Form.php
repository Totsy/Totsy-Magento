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

class Harapartners_Stockhistory_Block_Adminhtml_History_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
       						
	protected function _prepareForm() {

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post',
            //'enctype'  	 => 'multipart/form-data'
        ));
        

        $fieldset = $form->addFieldset('stockhistory', array('legend'=>Mage::helper('stockhistory')->__("PO Info")));
        
        $fieldset->addField('history_id', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('PO ID:'),
            'name'      => 'history_id',
        ));
        
        $fieldset->addField('entity_id', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Product ID:'),
            'name'      => 'entity_id',
        ));
        
        $fieldset->addField('product_name', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Product Name:'),
            'name'      => 'product_name',
        ));
        
        $fieldset->addField('size', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Size:'),
            'name'      => 'size',
        ));
        
        $fieldset->addField('color', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Color:'),
            'name'      => 'color',
        ));
        
        $fieldset->addField('product_sku', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Product SKU:'),
            'name'      => 'product_sku',
        ));
        
        $fieldset->addField('vendor_sku', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Vendor SKU:'),
            'name'      => 'vendor_sku',
        ));
        
        $fieldset->addField('qty_delta', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Quality Changed:'),
            'name'      => 'qty_delta',
        ));
        
        $fieldset->addField('unit_cost', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Unit Cost:'),
            'name'      => 'unit_cost',
        ));
        
        $fieldset->addField('total_cost', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Total Cost:'),
            'name'      => 'total_cost',
        ));
        
        $fieldset->addField('created_at', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Created At:'),
            'name'      => 'created_at',
        ));
        
        $fieldset->addField('updated_at', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Updated At:'),
            'name'      => 'updated_at',
        ));
        
        $fieldset->addField('status', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Status:'),
            'name'      => 'status',
        	'value'		=> Mage::helper('stockhistory')->getStatusOptions(),
        ));
        
        $fieldset->addField('comment', 'textarea', array(
            'label'     => Mage::helper('stockhistory')->__('Comment:'),
            'name'      => 'comment',
        ));
		//$configKey = 'text_content';		
		//$configText = Mage::getStoreConfig('config/textconfig_text/'.$configKey);
		
        //$form->setValues( array('file_import' => $configText) );
        
		if ( $formData = Mage::getSingleton('adminhtml/session')->getFormData() ){
            $form->setValues($formData);
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } elseif ( Mage::registry('po_data') ) {
            $form->setValues(Mage::registry('po_data')->getData());
        }
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
