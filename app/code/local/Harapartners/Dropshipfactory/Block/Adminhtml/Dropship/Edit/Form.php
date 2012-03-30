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
class Harapartners_Dropshipfactory_Block_Adminhtml_Dropship_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
	
	protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post',
            'enctype'  	 => 'multipart/form-data'
        ));
        

        $fieldset = $form->addFieldset('import', array('legend'=>Mage::helper('dropshipfactory')->__('Import Tracking File')));
        
        $fieldset->addField('tracking_import', 'file', array(
            'label'     => Mage::helper('dropshipfactory')->__('Please import file:'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'tracking_import',
        	'note'		=> Mage::helper('dropshipfactory')->__('File type should be .csv')
        ));
        
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
