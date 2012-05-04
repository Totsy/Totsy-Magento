<?php
/**
 * @category    Totsy
 * @package     Totsy\Api\Block\Adminhtml\Client\Edit
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Api_Block_Adminhtml_Client_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post',
        ));

        $fieldset = $form->addFieldset(
            'totsyapi',
            array('legend'=>Mage::helper('totsyapi')->__("Totsy API Client"))
        );

        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('totsyapi')->__('Client Name'),
            'name'      => 'name',
            'required'  => 'true',
        ));

        $fieldset->addField('contact_info', 'text', array(
            'label'     => Mage::helper('totsyapi')->__('Primary Contact'),
            'name'      => 'contact_info',
        ));

        $fieldset->addField('authorization', 'text', array(
            'label'     => Mage::helper('totsyapi')->__('Authorization Token'),
            'name'      => 'authorization',
            'note'      => Mage::helper('totsyapi')->__('Leave empty to auto-generate an Authorization Token value.')
        ));

        $fieldset->addField('active', 'checkbox', array(
            'label'     => Mage::helper('totsyapi')->__('Active'),
            'name'      => 'active',
        ));

        if ($formData = Mage::getSingleton('adminhtml/session')->getFormData()){
            $form->setValues($formData);
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
