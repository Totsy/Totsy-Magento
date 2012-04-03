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

		$objectId = $this->getRequest()->getParam('id');
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $objectId)),
            'method'    => 'post',
            //'enctype'  	 => 'multipart/form-data'
        ));
        

        $fieldset = $form->addFieldset('vendor', array('legend'=>Mage::helper('stockhistory')->__("Vendor Info")));
        
        if(!!$objectId){
	        $fieldset->addField('id', 'label', array(
	            'label'     => Mage::helper('stockhistory')->__('Vendor ID:'),
	            'name'      => 'id',
	        ));
		}
        
        $fieldset->addField('vendor_name', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Vendor Name:'),
            'name'      => 'vendor_name',
        	//'required'	=> true,
        ));
        
        $fieldset->addField('vendor_code', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Vendor Code:'),
            'name'      => 'vendor_code',
        	'required'	=> true,
        ));
        
        $fieldset->addField('vendor_type', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Vendor Type:'),
            'name'      => 'vendor_type',
        	//'required'	=> true,
        ));
        
        $fieldset->addField('contact_person', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Contact Person:'),
            'name'      => 'contact_person',
        ));
        
        $fieldset->addField('email_list', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Email List:'),
            'name'      => 'email_list',
        	'required'	=> true,
        	//'class'		=> 'validate-email',
        ));
        
        $fieldset->addField('phone', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Phone:'),
            'name'      => 'phone',
        	'class'		=>	'validate-phoneLax',
        ));
        
        $fieldset->addField('address', 'text', array(
            'label'     => Mage::helper('stockhistory')->__('Address:'),
            'name'      => 'address',
        ));
        
        $fieldset->addField('comment', 'textarea', array(
            'label'     => Mage::helper('stockhistory')->__('Comment:'),
            'name'      => 'comment',
        ));
		
		if ( Mage::registry('vendor_data') ) {
            $form->setValues(Mage::registry('vendor_data'));
        }
//		if ( $formData = Mage::getSingleton('adminhtml/session')->getVendorFormData() ){
//            $form->setValues($formData);
//            Mage::getSingleton('adminhtml/session')->setVendorFormData(null);
//        } elseif ( Mage::registry('vendor_data') ) {
//            $form->setValues(Mage::registry('vendor_data')->getData());
//        }
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
