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

class Harapartners_Affiliate_Block_Adminhtml_Record_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    
	       						
	protected function _prepareForm() {
		$affiliateHelper = Mage::helper('affiliate');
		
//		if(!!Mage::registry('affiliatePixelsCount')){
//			$pixelCount = Mage::registry('affiliatePixelsCount');
//		}
		
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));
        
        $fieldset = $form->addFieldset('affiliate', array('legend'=>$affiliateHelper->__('Record')));
        $fieldset->addType('trackingcode', 'Harapartners_Affiliate_Block_Adminhtml_Widget_Form_Element_Trackingcode');
        
        $fieldset->addField('affiliate_name', 'text', array(
            'label'     => $affiliateHelper->__('Affiliate Name'),
            'name'      => 'affiliate_name',
            'required'  => true,
        	'note'		=> '255 characters max.'
        ));
        
        $fieldset->addField('affiliate_code', 'text', array(
            'label'     => $affiliateHelper->__('Affiliate Code'),
            'name'      => 'affiliate_code',
            'required'  => true,
        	'note'		=> 'Alpha-numeric and underscore only. All characters in lower case. 255 characters max.'
        ));
        
        $fieldset->addField('type', 'select', array(
            'label'     => $affiliateHelper->__('Type'),
            'name'      => 'type',
            'required'  => true,
        	'values'    => $affiliateHelper->getFormTypeArray()
        ));
        
        $fieldset->addField('status', 'select', array(
            'label'     => $affiliateHelper->__('Status'),
            'name'      => 'status',
            'required'  => true,
        	'values'    => $affiliateHelper->getFormStatusArray()
        ));
        
        $fieldset->addField('tracking_code', 'trackingcode', array(
			'label'		=> $affiliateHelper->__('Tracking Code'),
			'name'		=> 'tracking_code',
        	'note'		=> 'You must "Confirm" if you want your edits to be saved. Empty fields are cleaned automatically before save.'
		));
        
        $fieldset->addField('sub_affiliate_code', 'textarea', array(
            'label'     => $affiliateHelper->__('Sub Affiliate Code'),
            'name'      => 'sub_affiliate_code',
        	'note'		=> 'Comma delimited. Each sub-code must be alpha-numeric and underscore only. All characters in lower case.'        
        ));
        
        $fieldset->addField('comment', 'textarea', array(
            'label'     => $affiliateHelper->__('Coment'),
            'name'      => 'comment'
        ));
        
        if ( Mage::registry('affiliate_form_data') ) {
            $form->setValues(Mage::registry('affiliate_form_data'));
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }    

}