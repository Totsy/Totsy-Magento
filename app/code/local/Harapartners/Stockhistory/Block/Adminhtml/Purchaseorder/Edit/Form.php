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

class Harapartners_Stockhistory_Block_Adminhtml_Purchaseorder_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
       						
	protected function _prepareForm() {
		
		//$dataObject = new Varien_Object(Mage::registry('po_data'));
		$helper = Mage::helper('stockhistory');
		$data = $this->getRequest()->getParams();
		
		
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post',
            //'enctype'  	 => 'multipart/form-data'
        ));
        
		
        $fieldset = $form->addFieldset('vendor', array('legend'=>$helper->__("Vendor Info")));
		
        //Adding PO without vendor_id is not allowed
        if(isset($data['vendor_id']) && !! $data['vendor_id']){
	        $fieldset->addField('vendor_id', 'text', array(
		            	'label'     => $helper->__('Vendor ID:'),
		            	'name'      => 'vendor_id',
		        		'readonly' 	=> true,
		        		'value'		=> $data['vendor_id'],//$dataObject->getVendorId(),
		        		'required'	=> true,
	        			'note'		=> 'Read only field. Each purchase order must be associated to a given vendor.'
	        ));
        }else{
        	$fieldset->addField('vendor_id', 'text', array(
		            	'label'     => $helper->__('Vendor ID:'),
		            	'name'      => 'vendor_id',
		        		'readonly' 	=> true,
		        		'required'	=> true,
	        			'note'		=> 'Read only field. Each purchase order must be associated to a given vendor.'
	        ));
        }
        
        $fieldset->addField('name', 'text', array(
            'label'     => $helper->__('Purchase Order Title:'),
            'name'      => 'name',
        	'required'	=> true,
        ));
        
        
        $fieldset->addField('comment', 'textarea', array(
            'label'     => $helper->__('Comment:'),
            'name'      => 'comment',
        ));
		
        //$form->setValues($dataObject->getData());
		if ( Mage::registry('po_data') ) {
            $form->setValues(Mage::registry('po_data'));
        }
//		if ( $formData = Mage::getSingleton('adminhtml/session')->getPoFormData() ){
//            $form->setValues($formData);
//            Mage::getSingleton('adminhtml/session')->setPoFormData(null);
//        } elseif ( Mage::registry('po_data') ) {
//            $form->setValues(Mage::registry('po_data')->getData());
//        }
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
