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
        
		$data = $this->getRequest()->getParams();
        $fieldset = $form->addFieldset('stockhistory', array('legend'=>Mage::helper('stockhistory')->__("PO Info")));
        if(isset($data['po_id']) && !!$data['po_id']){
        	$fieldset->addField('po_id', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('PO ID:'),
            'name'      => 'po_id',
        	'readonly' 	=> true,
        	'value'		=> $data['po_id'],
        	'required'	=> true,
        	));
        }else{
	        $fieldset->addField('po_id', 'text', array(
	            'label'     => Mage::helper('stockhistory')->__('PO ID:'),
	            'name'      => 'po_id',
	        	'required'	=> true
	        ));
        }
        if(isset($data['vendor_id']) && !!$data['vendor_id']){
        	$fieldset->addField('vendor_id', 'label', array(
	            'label'     => Mage::helper('stockhistory')->__('Vendor ID:'),
	            'name'      => 'vendor_id',
	        	'required'	=> true,
        		'readonly'	=> true,
        		'value'		=> $data['vendor_id'],
	        ));
        }else{
	        $fieldset->addField('vendor_id', 'text', array(
	            'label'     => Mage::helper('stockhistory')->__('Vendor ID:'),
	            'name'      => 'vendor_id',
	        	'required'	=> true
	        ));
        }
        $fieldset->addField('product_id', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Product ID:'),
            'name'      => 'product_id',
        	'required'	=> true
        ));
        
        $fieldset->addField('category_id', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Category ID:'),
            'name'      => 'category_id',
        	'required'	=> true
        ));
        
        $fieldset->addField('product_sku', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Product SKU:'),
            'name'      => 'product_sku',
        ));
        
        $fieldset->addField('vendor_sku', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Vendor SKU:'),
            'name'      => 'vendor_sku',
        ));
        
        $fieldset->addField('unit_cost', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Unit Cost:'),
            'name'      => 'unit_cost',
        	'required'	=> true
        ));
        
        $fieldset->addField('qty_delta', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Qty Changed:'),
            'name'      => 'qty_delta',
        	'required'	=> true
        ));

        $fieldset->addField('created_at', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Created At:'),
            'name'      => 'created_at',
        ));
        
        $fieldset->addField('updated_at', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Updated At:'),
            'name'      => 'updated_at',
        ));
        
        $fieldset->addField('action_type', 'select', array(
            'label'     => Mage::helper('stockhistory')->__('Action:'),
            'name'      => 'action_type',
        	'values'		=> Mage::helper('stockhistory')->getStatusOptions(),
        ));
        
        $fieldset->addField('comment', 'textarea', array(
            'label'     => Mage::helper('stockhistory')->__('Comment:'),
            'name'      => 'comment',
        ));
		//$configKey = 'text_content';		
		//$configText = Mage::getStoreConfig('config/textconfig_text/'.$configKey);
		
        //$form->setValues( array('file_import' => $configText) );
        
		if ( $formData = Mage::getSingleton('adminhtml/session')->getTransFormData() ){
            $form->setValues($formData);
            Mage::getSingleton('adminhtml/session')->setTransFormData(null);
        } elseif ( Mage::registry('trans_data') ) {
            $form->setValues(Mage::registry('trans_data')->getData());
        }
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
