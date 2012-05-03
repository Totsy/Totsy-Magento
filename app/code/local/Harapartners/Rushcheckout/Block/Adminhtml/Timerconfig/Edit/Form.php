<?php
class Harapartners_Rushcheckout_Block_Adminhtml_Timerconfig_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    
    protected function _prepareForm() {

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));
        

        $fieldset = $form->addFieldset('timer', array('legend'=>Mage::helper('rushcheckout')->__('Timer Setting')));
        
        $fieldset->addField('timer_config', 'text', array(
            'label'     => Mage::helper('rushcheckout')->__('Cart Time'),
            'class'     => 'validate-greater-than-zero',
            'required'  => true,
            'name'      => 'timer_config',
            'note'        => Mage::helper('rushcheckout')->__('Please input time in seconds')
        ));

        //set form data, get data first
        //$form->setValues('Enable'); //$formConfigData) $configurationValue ;
        $configKey = 'limit_timer';        
        $configTimer = Mage::getStoreConfig('config/rushcheckout_timer/'.$configKey);
        
        $form->setValues( array('timer_config' => $configTimer) );
        
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}