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
            'required'  => true,
            'time'      => true,
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'style'     => 'width: 140px;'
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

        $fieldset->addField('name', 'text', array(
            'label'     => $helper->__('Name'),
            'name'      => 'name',
            'required'  => true,
            'note'      => 'Must contain leading / character. 255 characters max.'
        ));

        $field = $fieldset->addField('image', 'file', array(
            'label'     => $helper->__('Image'),
            'name'      => 'image',
            'value'     => 'Upload',
            'note'      => 'Only jpg, gif or png extentions allowed.'
        ));

        $fieldset->addField('link', 'text', array(
            'label'     => $helper->__('Link'),
            'name'      => 'link',
            'required'  => false,
            'note'      => 'Followed with / character. 255 characters max.'
        ));

        $fieldset->addField('at_home', 'select', array(
            'label'     => $helper->__('At Home Page'),
            'name'      => 'at_home',
            'value'     => '-1',
            'values'    => array(
                array('value'=>'-1', 'label'=> 'Please select ...'),
                array('value'=>0, 'label'=> 'NO'),
                array('value'=>1, 'label'=> 'YES'),
            ),
            'disabled'  => false,
            'readonly'  => false,
            'required'  => true
        ));

        $fieldset->addField('at_events', 'textarea', array(
            'label'     => $helper->__('At Events Pages'),
            'name'      => 'at_events',
            'required'  => false,
            'note'      => 'Provide a list of events ids separeted by \',\' or new line'
        ));

        $fieldset->addField('at_products', 'textarea', array(
            'label'     => $helper->__('At Products Pages'),
            'name'      => 'at_products',
            'required'  => false,
            'note'      => 'Provide a list of products ids separeted by \',\' or new line' 
        ));



        $fieldset->addField('end_at', 'date', array(
            'label'     => $helper->__('End Date'),
            'name'      => 'end_at',
            'required'  => true,
            'time'      => true,
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'style'     => 'width: 140px;' 
        ));

        if ( Mage::registry('promotions_banner_form_data') ) {
            $form->setValues(Mage::registry('promotions_banner_form_data'));
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}