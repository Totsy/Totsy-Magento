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

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit_Tab_Export_Condition extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('profile_data');
        $source = Mage::getSingleton('urapidflow/source');

        //$form = new Varien_Data_Form(array('id' => 'edit_form1', 'action' => $this->getData('action'), 'method' => 'post'));
        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rule_');

        // conditions
        $fieldset = $form->addFieldset('conditions_fieldset', array(
            'legend'=>Mage::helper('urapidflow')->__('Export only products matching the following conditions (leave blank for all products)')
        ))->setRenderer($this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('urapidflowadmin/adminhtml_profile/newConditionHtml', array('form'=>'conditions')))
        );

        $fieldset->addField('conditions_post', 'text', array(
            'name' => 'conditions_post',
            'label' => Mage::helper('urapidflow')->__('Export Conditions'),
            'title' => Mage::helper('urapidflow')->__('Export Conditions'),
            'value' => $model->getConditions(),
        ))->setRule($model->getConditionsRule())->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $fieldset = $form->addFieldset('export_additional_conditions', array('legend'=>$this->__('Additional Conditions')));

        $fieldset->addField('export_skip_out_of_stock', 'select', array(
            'label'     => $this->__('Skip out of stock products'),
            'name'      => 'options[export][skip_out_of_stock]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $model->getData('options/export/skip_out_of_stock'),
        ));
        
        $fieldset->addField('export_skip_configurable_simples', 'select', array(
            'label'     => $this->__('Do not export simple products that are used in configurable'),
            'name'      => 'options[export][skip_configurable_simples]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $model->getData('options/export/skip_configurable_simples'),
        ));
        
        $fieldset->addField('export_websites_filter', 'multiselect', array(
            'label'     => $this->__('Websites filter'),
            'name'      => 'options[export][websites_filter]',
            'values'    => Mage::getSingleton('adminhtml/system_config_source_website')->toOptionArray(),
            'value'     => $model->getData('options/export/websites_filter'),
        ));

        $this->setForm($form);

        return parent::_prepareForm();
    }
}