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

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit_Tab_Schedule extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $hlp = Mage::helper('urapidflow');
        $source = Mage::getSingleton('urapidflow/source');

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('schedule_form', array('legend'=>$this->__('Schedule Options')));

        $fieldset->addField('schedule_enable', 'select', array(
            'label'     => $this->__('Enable Schedule'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'schedule_enable',
            'values'    => $source->setPath('yesno')->toOptionArray(),
        ));

        $fieldset->addField('schedule_hours', 'multiselect', array(
            'label'     => $this->__('Hours'),
            'name'      => 'schedule_week_days',
            'values'    => $source->setPath('schedule_hours')->toOptionArray(),
        ));

        $fieldset->addField('schedule_week_days', 'multiselect', array(
            'label'     => $this->__('Week Days'),
            'name'      => 'schedule_week_days',
            'values'    => $source->setPath('schedule_week_days')->toOptionArray(),
        ));

        $fieldset->addField('schedule_month_days', 'multiselect', array(
            'label'     => $this->__('Month Days'),
            'name'      => 'schedule_month_days',
            'values'    => $source->setPath('schedule_month_days')->toOptionArray(),
        ));

        $fieldset->addField('schedule_months', 'multiselect', array(
            'label'     => $this->__('Months'),
            'name'      => 'schedule_months',
            'values'    => $source->setPath('schedule_months')->toOptionArray(),
        ));

        if (($profile = Mage::registry('profile_data'))) {
            $form->setValues($profile->getData());
        }

        return parent::_prepareForm();
    }
}