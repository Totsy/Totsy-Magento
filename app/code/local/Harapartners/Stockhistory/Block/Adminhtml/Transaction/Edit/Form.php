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

class Harapartners_Stockhistory_Block_Adminhtml_Transaction_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
       						
	protected function _prepareForm() {
		$dataObject = new Varien_Object(Mage::registry('stockhistory_transaction_data'));
		$helper = Mage::helper('stockhistory');
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post',
            //'enctype'  	 => 'multipart/form-data'
        ));
        
        $fieldset = $form->addFieldset('stockhistory', array('legend'=>$helper->__("Transaction Info")));
        
		if(!!$dataObject->getData('vendor_id')){
        	$fieldset->addField('vendor_id', 'text', array(
		            'label'     => $helper->__('Vendor ID:'),
		            'name'      => 'vendor_id',
		        	'required'	=> true,
	        		'readonly'	=> true,
	        		'note'		=> 'Read only field.'
	        ));
        }else{
	        $fieldset->addField('vendor_id', 'text', array(
		            'label'     => $helper->__('Vendor ID:'),
		            'name'      => 'vendor_id',
		        	'required'	=> true,
		        	'note'		=> 'Once saved, this field <b>CANNOT</b> be modified.'
	        ));
        }
        
        if(!!$dataObject->getData('po_id')){
        	$fieldset->addField('po_id', 'text', array(
		            'label'     => $helper->__('PO ID:'),
		            'name'      => 'po_id',
		        	'readonly' 	=> true,
		        	'required'	=> true,
        			'note'		=> 'Read only field.'
		     ));
        }else{
	        $fieldset->addField('po_id', 'text', array(
		            'label'     => $helper->__('PO ID:'),
		            'name'      => 'po_id',
		        	'required'	=> true,
		        	'note'		=> 'Once saved, this field <b>CANNOT</b> be modified.'
	        ));
        }
        
        if(!!$dataObject->getData('vendor_code')){
	         $fieldset->addField('vendor_code', 'text', array(
		            'label'     => $helper->__('Vendor Code:'),
		            'name'      => 'vendor_code',
		         	'required'	=> true,
		         	'readonly'	=> true,
		         	'note'		=> 'Read only field.'
	        ));
        }else{
	        $fieldset->addField('vendor_code', 'text', array(
		            'label'     => $helper->__('Vendor Code:'),
		            'name'      => 'vendor_code',
		        	'required'	=> true,
		        	'note'		=> 'Once saved, this field <b>CANNOT</b> be modified.'
	        ));
        }
        
		if(!!$dataObject->getData('category_id')){
			$fieldset->addField('category_id', 'text', array(
	            'label'     => $helper->__('Category ID:'),
	            'name'      => 'category_id',
				'required'	=> true,
	        	'readonly'	=> true,
		   		'note'		=> 'Read only field.'
	        ));
        }else{
	        $fieldset->addField('category_id', 'text', array(
	            'label'     => $helper->__('Category ID:'),
	            'name'      => 'category_id',
	        	'required'	=> true,
	        	'note'		=> 'Category ID of the event'
	        ));
        }
        
        
        $fieldset->addField('product_id', 'text', array(
            'label'     => $helper->__('Product ID:'),
            'name'      => 'product_id',
        	'required'	=> true
        ));
        
//        $fieldset->addField('product_sku', 'text', array(
//            'label'     => $helper->__('Product SKU:'),
//            'name'      => 'product_sku',
//        ));
        
        $fieldset->addField('unit_cost', 'text', array(
            'label'     => $helper->__('Unit Cost:'),
            'name'      => 'unit_cost',
        	'required'	=> true
        ));
        
        $fieldset->addField('qty_delta', 'text', array(
            'label'     => $helper->__('Qty Changed:'),
            'name'      => 'qty_delta',
        	'required'	=> true,
        	'note'		=> 'Qty changed will also be applied to the Product Stock',
        ));
        
        $fieldset->addField('action_type', 'text', array(
            //'label'     => $helper->__('Action:'),
            'name'      => 'action_type',
        	'readonly'	=> true,
        	'style'		=> 'display:none',
        	//'values'		=> $helper->getFormTransactionTypeArray(),
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
