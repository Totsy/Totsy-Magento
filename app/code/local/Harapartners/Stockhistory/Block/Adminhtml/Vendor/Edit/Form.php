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
        $dataObject = new Varien_Object(Mage::registry('stockhistory_vendor_data'));
        $helper = Mage::helper('stockhistory');
        
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $dataObject->getId())),
            'method'    => 'post',
            //'enctype'       => 'multipart/form-data'
        ));
        

        $fieldset = $form->addFieldset('vendor', array('legend'=>$helper->__("Vendor Info")));
        
        if(!!$dataObject->getId()){
            $fieldset->addField('id', 'label', array(
                'label'     => $helper->__('Vendor ID:'),
                'name'      => 'id',
            ));
        }
        
        $fieldset->addField('vendor_name', 'text', array(
            'label'     => $helper->__('Vendor Name:'),
            'name'      => 'vendor_name',
            'required'    => true,
        ));
        
        //Harapartners, Jun/Song, very important restriction, 'vendor_code' <=> product relationship is locked. Once being set, CANNOT be changed
        if(!!$dataObject->getId()){
            $fieldset->addField('vendor_code', 'label', array(
                'label'     => $helper->__('Vendor Code:'),
                'name'      => 'vendor_code',
            ));
        }else{
            $fieldset->addField('vendor_code', 'text', array(
                'label'     => $helper->__('Vendor Code:'),
                'name'      => 'vendor_code',
                'required'    => true,
                'note'        => 'Once saved, this field <b>CANNOT</b> be modified. Alpha-numeric with underscore, lowercase only.'
            ));
        }
        
        $fieldset->addField('vendor_type', 'select', array(
            'label'     => $helper->__('Vendor Type:'),
            'name'      => 'vendor_type',
            'required'    => true,
            'values'    => $helper->getFormVendorTypeArray(),
        ));
        
        $fieldset->addField('status', 'select', array(
            'label'     => $helper->__('Status:'),
            'name'      => 'status',
            'required'    => true,
            'values'    => $helper->getFormVendorStatusArray(),
        ));
        
        $fieldset->addField('contact_person', 'text', array(
            'label'     => $helper->__('Contact Person:'),
            'name'      => 'contact_person',
        ));
        
        $fieldset->addField('email_list', 'text', array(
            'label'     => $helper->__('Email List:'),
            'name'      => 'email_list',
            'note'        => 'Multiple emails allowed, comma delimited'
        ));
        
        $fieldset->addField('phone', 'text', array(
            'label'     => $helper->__('Phone:'),
            'name'      => 'phone',
            'class'        =>    'validate-phoneLax',
        ));
        
        $fieldset->addField('address', 'textarea', array(
            'label'     => $helper->__('Address:'),
            'name'      => 'address',
        ));
        
        $fieldset->addField('comment', 'textarea', array(
            'label'     => $helper->__('Comment:'),
            'name'      => 'comment',
        ));
        
        $fieldset->addField('payment_terms', 'textarea', array(
            'label'     => $helper->__('Payment Terms:'),
            'name'      => 'payment_terms',
        ));
        
        $fieldset->addField('banking_info', 'textarea', array(
            'label'     => $helper->__('Banking Info:'),
            'name'      => 'banking_info',
        ));
        
        $form->setValues($dataObject->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}