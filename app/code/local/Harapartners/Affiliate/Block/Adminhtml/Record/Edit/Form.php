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
    protected  $_statusOption = array(
       						array('label'=>'Enable','value'=>1),
       						array('label'=>'Disable','value'=>0));
    protected  $_typeOption = array(
       						array('label'=>'Standard','value'=>'Standard'),
       						array('label'=>'Super Affiliate','value'=>'Super Affiliate'));
    protected  $_pageArray = array(
    							array('label'=>'Landing_page','value'=>'landing'),
	       						array('label'=>'After_registering','value'=>'after_reg'),
	       						array('label'=>'Login_page','value'=>'login'),
	       						array('label'=>'Sales_page','value'=>'sales'),
	       						array('label'=>'Product_page','value'=>'product'),
	       						array('label'=>'Event_page','value'=>'event'),
	       						array('label'=>'Order_confirmation_page','value'=>'order'),
	       						array('label'=>'Order_confirmation_page(spinback)','value'=>'order_spinback'),
	       						array('label'=>'Invite_page(spinback)','value'=>'invite_spinback'),
	       						);
	protected function _prepareForm() {
		
		$yesno = Mage::getModel('adminhtml/system_config_source_yesno');
		if(!!Mage::registry('affiliatePixelsCount')){
			$pixelCount = Mage::registry('affiliatePixelsCount');
		}
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));
        
        
        $fieldset = $form->addFieldset('affiliate', array('legend'=>Mage::helper('affiliate')->__('Record')));
        $fieldset->addField('affiliate_code', 'text', array(
            'label'     => Mage::helper('affiliate')->__('Affiliate Code'),
            'name'      => 'affiliate_code',
            'required'  => true,
        ));
        $fieldset->addField('sub_affiliate_code', 'text', array(
            'label'     => Mage::helper('affiliate')->__('Sub Affiliate Code'),
            'name'      => 'sub_affiliate_code',           
        ));
        $fieldset->addField('type', 'select', array(
            'label'     => Mage::helper('affiliate')->__('Type'),
            'name'      => 'type',
            'required'  => true,
        	'values'    => $this->_typeOption,
        ));        
        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('affiliate')->__('Status'),
            'name'      => 'status',
            'required'  => true,
        	'values'    => $this->_statusOption,
//          'note'		=> Mage::helper('affiliate')->__('Enable Status'),
        ));
        $fieldset->addField('invitation_code', 'text', array(
            'label'     => Mage::helper('affiliate')->__('Invitation Code'),
            'name'      => 'invitation_code',
//          'required'  => true,
        ));        
//        $fieldset->addField('tracking_code', 'text', array(
//            'label'     => Mage::helper('affiliate')->__('Tracking Code'),
//            'name'      => 'tracking_code',
//           'required'  => true,
//        ));
		$i=0;
		while ($i<$pixelCount) {	
			$j=$i+1;			
	        $fieldset->addField('pixel'.$i.'separator', 'label', array(
	            'label'     => Mage::helper('affiliate')->__('Pixel #'.$j.':'),
	            'name'      => 'pixels'.$i.'separator',
	        ));			
	        $fieldset->addField('pixels'.$i.'enable', 'select', array(
	            'label'     => Mage::helper('affiliate')->__('Pixel Enable'),
	            'name'      => 'pixels'.$i.'enable',
//          	'required'  => true,
	        	'values'    => array(
	       						array('label'=>'Enable','value'=>'true'),
	       						array('label'=>'Disable','value'=>'false')),
	        ));
	        $fieldset->addField('pixels'.$i.'page', 'multiselect', array(
	            'label'     => Mage::helper('affiliate')->__('Selct page'),
	            'name'      => 'pixels'.$i.'page',
	        	'values'    => $this->_pageArray,
	        ));
	        $fieldset->addField('pixels'.$i.'pixel', 'textarea', array(
	            'label'     => Mage::helper('affiliate')->__('Pixel'),
	            'name'      => 'pixels'.$i.'pixel',
//         		'required'  => true,
	        ));	
	        $i++;
		}
		$j=$i+1;
			$fieldset->addField('pixel'.$i.'separator', 'label', array(
	            'label'     => Mage::helper('affiliate')->__('Add Pixel #'.$j.':'),
	            'name'      => 'pixels'.$i.'separator',
	        ));			
	        $fieldset->addField('pixels'.$i.'enable', 'select', array(
	            'label'     => Mage::helper('affiliate')->__('Pixel Enable'),
	            'name'      => 'pixels'.$i.'enable',
//          	'required'  => true,
	        	'values'    => array(
	       						array('label'=>'Enable','value'=>'true'),
	       						array('label'=>'Disable','value'=>'false')),
	        ));
	        $fieldset->addField('pixels'.$i.'page', 'multiselect', array(
	            'label'     => Mage::helper('affiliate')->__('Selct page'),
	            'name'      => 'pixels'.$i.'page',
	        	'values'    => $this->_pageArray,
	        ));
	        $fieldset->addField('pixels'.$i.'pixel', 'textarea', array(
	            'label'     => Mage::helper('affiliate')->__('Pixel'),
	            'name'      => 'pixels'.$i.'pixel',
//          	'required'  => true,
	        ));	

        if ( $formData = Mage::getSingleton('adminhtml/session')->getAffiliateFormData() ){
            $form->setValues($formData);
            Mage::getSingleton('adminhtml/session')->setAffiliateFormData(null);
        } elseif ( Mage::registry('affiliate_form_data') ) {
            $form->setValues(Mage::registry('affiliate_form_data'));
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }    

}