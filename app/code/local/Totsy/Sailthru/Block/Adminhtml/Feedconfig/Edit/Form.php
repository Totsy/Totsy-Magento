<?php

class Totsy_Sailthru_Block_Adminhtml_Feedconfig_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() {
        $helper = Mage::helper('sailthru');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('sailthru', array('legend'=>$helper->__('Feed Cofnig Info')));

        $fieldset->addField('type', 'select', array(
            'label'     => $helper->__('Type'),
            'name'      => 'type',
            'value'     => '-1',
            'values'    => Totsy_Sailthru_Helper_Feedconfig::mapTypesSelect(),
            'disabled'  => false,
            'readonly'  => false,
            'required'  => true,
        ));

        $fieldset->addField('order', 'select', array(
            'label'     => $helper->__('Order'),
            'name'      => 'order',
            'value'     => '-1',
            'values'    => Totsy_Sailthru_Helper_Feedconfig::mapOrdersSelect(),
            'disabled'  => false,
            'readonly'  => false,
            'required'  => true,
        ));

        $fieldset->addField('start_at_day', 'date', array(
            'label'     => $helper->__('Start Date'),
            'name'      => 'start_at_day',
            'required'  => false,
            'time'      => true,
            'format'    => Varien_Date::DATE_INTERNAL_FORMAT,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'style'     => 'width: 140px;',
            'note'      => 'IMPORTANT! This field is REQUIRED for events feed.<br>'
        ));

        $fieldset->addField('start_at_time', 'select', array(
            'label'     => $helper->__('Time'),
            'name'      => 'start_at_time',
            'value'     => '-1',
            'values'    => Totsy_Sailthru_Helper_Feedconfig::mapTimeSelect(),
            'disabled'  => false,
            'readonly'  => false,
            'required'  => false,
        ));

        $fieldset->addField('include', 'text', array(
            'label'     => $helper->__('Include'),
            'name'      => 'include',
            'required'  => false,
            'note'      => 'IMPORTANT! This field is REQUIRED for products feed.<br>'.
                           'Provide a list of events or products ids separeted by \',\' or new line'
        ));

        $fieldset->addField('exclude', 'text', array(
            'label'     => $helper->__('Exclude'),
            'name'      => 'exclude',
            'required'  => false,
            'note'      => 'Provide a list of events or products ids separeted by \',\' or new line'
        ));

        $fieldset->addField('filter', 'text', array(
            'label'     => $helper->__('Filter'),
            'name'      => 'filter',
            'required'  => false,
            'note'      => 'Provide a list of parameters separeted by \',\' or new line'
        ));

        if ( Mage::registry('sailthru_feedconfig_form_data') ) {
            $form->setValues(Mage::registry('sailthru_feedconfig_form_data'));
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}