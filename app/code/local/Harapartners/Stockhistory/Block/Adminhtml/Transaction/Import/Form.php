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

class Harapartners_Stockhistory_Block_Adminhtml_Transaction_Import_Form extends Mage_Adminhtml_Block_Widget_Form{
	
	protected function _prepareForm() {

        $form = new Varien_Data_Form(array(
            'id'        => 'import_form',
            'action'    => $this->getUrl('*/*/saveImport', array('id' => $this->getRequest()->getParam('stock_import'))),
            'method'    => 'post',
            'enctype'  	 => 'multipart/form-data'
        ));
        

        $fieldset = $form->addFieldset('stockhistory_import', array('legend'=>Mage::helper('stockhistory')->__('Import CSV Files')));
        
        $fieldset->addField('stock_import', 'file', array(
            'label'     => Mage::helper('stockhistory')->__('Please import file:'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'stock_import',
        	'note'		=> Mage::helper('stockhistory')->__('File type should be .csv')
        ));
        
//		if($formData = Mage::getSingleton('adminhtml/session')->getFormData()) {
//            $form->setValues($formData);
//        } elseif ( Mage::registry('itemqueue') ) {
//            $form->setValues(Mage::registry('itemqueue')->getData());
//        }
        
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}