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

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit_Tab_Export extends Mage_Adminhtml_Block_Widget_Form
{
    public function _prepareForm()
    {
        $hlp = Mage::helper('urapidflow');
        $source = Mage::getSingleton('urapidflow/source');

        $profile = Mage::registry('profile_data');

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('export_options_form', array('legend'=>$this->__('Export Options')));

        $fieldset->addField('export_image_files', 'select', array(
            'label'     => $this->__('Auto-export image files'),
            'name'      => 'options[export][image_files]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/export/image_files'),
        ));
        $fieldset->addField('export_image_https', 'select', array(
            'label'     => $this->__('Export Image URLs as HTTPS'),
            'name'      => 'options[export][image_https]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/export/image_https'),
        ));
        $fieldset->addField('export_invalid_values', 'select', array(
            'label'     => $this->__('Export invalid values'),
            'name'      => 'options[export][invalid_values]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/export/invalid_values'),
        ));
        $fieldset->addField('export_internal_values', 'select', array(
            'label'     => $this->__('Export internal values'),
            'name'      => 'options[export][internal_values]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/export/internal_values'),
        ));
        
        $fieldset->addField('export_configurable_qty_as_sum', 'select', array(
            'label'     => $this->__('Calculate qty of configurable products as sum of subproducts'),
            'name'      => 'options[export][configurable_qty_as_sum]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/export/configurable_qty_as_sum'),
        ));

        $fieldset = $this->getForm()->addFieldset('export_price_form', array('legend'=>$this->__('Price Options')));

        /*
        $fieldset->addField('export_use_final_price', 'select', array(
            'label'     => $this->__('Use Final Price'),
            'name'      => 'options[export][use_final_price]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/export/use_final_price'),
        ));
        $fieldset->addField('export_use_minimal_price', 'select', array(
            'label'     => $this->__('Use Minimal Price'),
            'name'      => 'options[export][use_minimal_price]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/export/use_minimal_price'),
        ));
        */
        $fieldset->addField('export_add_tax', 'select', array(
            'label'     => $this->__('Add Tax'),
            'name'      => 'options[export][add_tax]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/export/add_tax'),
        ));
        $fieldset->addField('export_markup', 'text', array(
            'label'     => $this->__('Add Markup (%)'),
            'name'      => 'options[export][markup]',
            'value'     => $profile->getData('options/export/markup'),
        ));

        return parent::_prepareForm();
    }
}