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

class Harapartners_Categoryevent_Block_Adminhtml_Config_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
	
	protected function _prepareForm() {
		
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));
        

        $fieldset = $form->addFieldset('pagenumber', array('legend'=>Mage::helper('categoryevent')->__('Start Page number Setting')));
        
        $fieldset->addField('pagenumber_config', 'text', array(
            'label'     => Mage::helper('categoryevent')->__('Start Page number'),
            'class'     => 'validate-greater-than-zero',
            'required'  => true,
            'name'      => 'pagenumber_config',
        	'note'		=> Mage::helper('categoryevent')->__('Input page number to change the default display page number e.g. 4, 8, 12')
        ));

		$configKey = 'pagenumber_config';		
		$configTimer = Mage::getStoreConfig('config/catalog_page_number/'.$configKey);
		
        $form->setValues( array('pagenumber_config' => $configTimer) );
        
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}