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

class Harapartners_Stockhistory_Block_Adminhtml_Vendor_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
       						
	protected function _prepareForm() {

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post',
            //'enctype'  	 => 'multipart/form-data'
        ));
        

        $fieldset = $form->addFieldset('vendor', array('legend'=>Mage::helper('stockhistory')->__("Vendor Info")));
        
//        $fieldset->addField('id', 'text', array(
//            'label'     => Mage::helper('stockhistory')->__('Vendor ID:'),
//            'name'      => 'id',
//        ));
        
        
        $fieldset->addField('vendor_name', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Vendor Name:'),
            'name'      => 'vendor_name',
        ));
        
        $fieldset->addField('vendor_sku', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Vendor SKU:'),
            'name'      => 'vendor_sku',
        ));
        
        $fieldset->addField('contact_person', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Contact Person:'),
            'name'      => 'contact_person',
        ));
        
        $fieldset->addField('email', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Email:'),
            'name'      => 'email',
        ));
        
        $fieldset->addField('phone', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Phone:'),
            'name'      => 'phone',
        ));
        
        $fieldset->addField('created_at', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Created At:'),
            'name'      => 'created_at',
        ));
        
        $fieldset->addField('updated_at', 'label', array(
            'label'     => Mage::helper('stockhistory')->__('Updated At:'),
            'name'      => 'updated_at',
        ));
        
        $fieldset->addField('comment', 'textarea', array(
            'label'     => Mage::helper('stockhistory')->__('Comment:'),
            'name'      => 'comment',
        ));
		//$configKey = 'text_content';		
		//$configText = Mage::getStoreConfig('config/textconfig_text/'.$configKey);
		
        //$form->setValues( array('file_import' => $configText) );
        
		if ( $formData = Mage::getSingleton('adminhtml/session')->getVendorFormData() ){
            $form->setValues($formData);
            Mage::getSingleton('adminhtml/session')->setVendorFormData(null);
        } elseif ( Mage::registry('vendor_data') ) {
            $form->setValues(Mage::registry('vendor_data')->getData());
        }
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
