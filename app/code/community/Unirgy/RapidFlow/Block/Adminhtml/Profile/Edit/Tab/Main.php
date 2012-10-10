<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $hlp = Mage::helper('urapidflow');
        $source = Mage::getSingleton('urapidflow/source');

        $profile = Mage::registry('profile_data');
        $new = !$profile || !$profile->getId();

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('profile_form', array('legend'=>$this->__('Profile Information')));

        $fieldset->addField('title', 'text', array(
            'label'     => $this->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
        ));

        $fieldset->addField('profile_status', 'select', array(
            'label'     => $this->__('Profile Status'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'profile_status',
            'values'    => $source->setPath('profile_status')->toOptionArray(),
        ));

        if ($new) {
            $fieldset->addField('profile_type', 'select', array(
                'label'     => $this->__('Profile Type'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'profile_type',
                'values'    => $source->setPath('profile_type')->toOptionArray(),
            ));

            $fieldset->addField('data_type', 'select', array(
                'label'     => $this->__('Data Type'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'data_type',
                'values'    => $source->setPath('data_type')->toOptionArray(),
            ));
        }

        $oldWithDefaultWebsiteFlag = $source->withDefaultWebsite(!$profile || $profile->getDataType()!='sales');
        $fieldset->addField('store_id', 'select', array(
            'label'     => $this->__('Store View'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'store_id',
            'values'    => $source->setPath('stores')->toOptionArray(),
        ));
        $source->withDefaultWebsite($oldWithDefaultWebsiteFlag);

        $fieldset->addField('base_dir', 'text', array(
            'label'     => $this->__('File Location'),
            'name'      => 'base_dir',
            'note'      => $this->__('Leave empty for default'),
        ));

        $fieldset->addField('filename', 'text', array(
            'label'     => $this->__('File Name'),
            'required'  => true,
            'class'     => 'required-entry',
            'name'      => 'filename',
        ));

        if (!$new) {
            $fieldset->addField('profile_type', 'select', array(
                'label'     => $this->__('Profile Type'),
                'disabled'  => true,
                'name'      => 'profile_type',
                'values'    => $source->setPath('profile_type')->toOptionArray(),
            ));

            $fieldset->addField('data_type', 'select', array(
                'label'     => $this->__('Data Type'),
                'disabled'  => true,
                'name'      => 'data_type',
                'values'    => $source->setPath('data_type')->toOptionArray(),
            ));

            $fieldset->addField('run_status', 'select', array(
                'label'     => $this->__('Run Status'),
                'disabled'  => true,
                'name'      => 'run_status',
                'values'    => $source->setPath('run_status')->toOptionArray(),
            ));
            $fieldset->addField('invoke_status', 'select', array(
                'label'     => $this->__('Invoke Status'),
                'disabled'  => true,
                'name'      => 'invoke_status',
                'values'    => $source->setPath('invoke_status')->toOptionArray(),
            ));
        }

        if ($profile) {
            $form->setValues($profile->getData());
        }

        $fieldset = $form->addFieldset('log_form', array('legend'=>$this->__('Logging Options')));

        $fieldset->addField('minimum_log_level', 'select', array(
            'label'     => $this->__('Minimum Log Level'),
            'name'      => 'options[log][min_level]',
            'values'    => $source->setPath('log_level')->toOptionArray(),
            'value'     => $profile->getData('options/log/min_level'),
        ));

        if (!$new && in_array($profile->getDataType(), array('category','product_extra'))) {
            $fieldset = $form->addFieldset('category_specific_form', array('legend'=>$this->__('Category Options')));
            $fieldset->addField($profile->getProfileType().'_urlpath_prepend_root', 'select', array(
                'label'     => $this->__(
                    $profile->getProfileType()=='export'
                    ? 'Prepend Root Category Name To URL Paths'
                    : 'Use Prepended Root Category Name To URL Paths'
                ),
                'name'      => 'options['.$profile->getProfileType().'][urlpath_prepend_root]',
                'values'    => $source->setPath('yesno')->toOptionArray(),
                'value'     => $profile->getData('options/'.$profile->getProfileType().'/urlpath_prepend_root'),
                'note'      => $this->__(
                    'Serves as a workaround when there are multiple root categories with identical trees, or tree elements. <br>'
                    .'Hence subcategories within different root categories have identical url paths.'
                ),
            ));
        }

        return parent::_prepareForm();
    }
}