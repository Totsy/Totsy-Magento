<?php

class Totsy_Promotions_Block_Adminhtml_Banner_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() {
        $affiliateHelper = Mage::helper('promotions');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post',
            'enctype'   => 'multipart/form-data'
        ));

        $fieldset = $form->addFieldset('promotions', array('legend'=>$affiliateHelper->__('Banner Info')));

        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('promotions')->__('Active'),
            'name'      => 'is_active',
            'value'     => '-1',
            'values'    => array(
                array('value'=>'-1', 'label'=> 'Please select ...'),
                array('value'=>0, 'label'=> 'NO'),
                array('value'=>1, 'label'=> 'YES'),
            ),
            'disabled'  => false,
            'readonly'  => false,
            'required'  => true,
        ));

        $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('promotions')->__('Name'),
            'name'      => 'name',
            'required'  => true,
            'note'      => 'Must contain leading / character. 255 characters max.'
        ));

        $field = $fieldset->addField('image', 'file', array(
            'label'     => Mage::helper('promotions')->__('Image'),
            'name'      => 'image',
            'value'     => 'Upload',
            'note'      => 'Only jpg, gif or png extentions allowed.'
        ));

        $fieldset->addField('link', 'text', array(
            'label'     => Mage::helper('promotions')->__('Link'),
            'name'      => 'link',
            'required'  => false,
            'note'      => 'Followed with / character. 255 characters max.'
        ));

        $fieldset->addField('at_home', 'select', array(
            'label'     => Mage::helper('promotions')->__('At Home Page'),
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
            'label'     => Mage::helper('promotions')->__('At Events Pages'),
            'name'      => 'at_events',
            'required'  => false,
            'note'      => 'Provide a list of events ids separeted by \',\' or new line'
        ));

        $fieldset->addField('at_products', 'textarea', array(
            'label'     => Mage::helper('promotions')->__('At Products Pages'),
            'name'      => 'at_products',
            'required'  => false,
            'note'      => 'Provide a list of products ids separeted by \',\' or new line' 
        ));

        $fieldset->addField('start_at', 'date', array(
            'label'     => Mage::helper('promotions')->__('Start Date'),
            'name'      => 'start_at',
            'required'  => true,
            'time'      => true,
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'style'     => 'width: 140px;'
        ));

        $fieldset->addField('end_at', 'date', array(
            'label'     => Mage::helper('promotions')->__('End Date'),
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