<?php

class Totsy_Reward_Block_Adminhtml_Import_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() {
        $helper = Mage::helper('enterprise_reward');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post',
            'enctype'   => 'multipart/form-data'
        ));

        $fieldset = $form->addFieldset('enterprise_reward', array('legend'=>$helper->__('Credits')));

        $field = $fieldset->addField('file', 'file', array(
            'label'     =>$helper->__('Upload a File'),
            'name'      => 'file',
            'value'     => 'Upload',
            'note'      => 'Only csv file format is allowed.'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}