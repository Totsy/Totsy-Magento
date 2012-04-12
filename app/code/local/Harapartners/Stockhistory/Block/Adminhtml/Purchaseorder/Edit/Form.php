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
		
		$dataObject = new Varien_Object(Mage::registry('stockhistory_po_data'));
		$helper = Mage::helper('stockhistory');
		//$data = $this->getRequest()->getParams();
		
		
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $dataObject->getId())),
            'method'    => 'post',
            //'enctype'  	 => 'multipart/form-data'
        ));
        
		
        $fieldset = $form->addFieldset('purchase_order', array('legend'=>$helper->__("Purchase Order Info")));
		
        //Adding PO without vendor_id is not allowed
        if(!!$dataObject->getData('vendor_code')){
	        $fieldset->addField('vendor_code', 'text', array(
		            	'label'     => $helper->__('Vendor Code:'),
		            	'name'      => 'vendor_code',
		        		'readonly' 	=> true,
		        		'required'	=> true,
	        			'note'		=> 'Read only field. Each purchase order must be associated to a given vendor.'
	        ));
        }else{
        	$fieldset->addField('vendor_code', 'text', array(
		            	'label'     => $helper->__('Vendor Code:'),
		            	'name'      => 'vendor_code',
		        		'required'	=> true,
	        			'note'		=> 'Once saved, this field <b>CANNOT</b> be modified.'
	        ));
        }
        
        $fieldset->addField('name', 'text', array(
            'label'     => $helper->__('Purchase Order Name:'),
            'name'      => 'name',
        	'required'	=> true,
        ));
        
        $fieldset->addField('category_id', 'text', array(
            'label'     => $helper->__('Category/Event ID:'),
            'name'      => 'category_id',
        	'required'	=> true,
        ));
        
        $fieldset->addField('comment', 'textarea', array(
            'label'     => $helper->__('Comment:'),
            'name'      => 'comment',
        ));

        
		$form->setValues($dataObject->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
