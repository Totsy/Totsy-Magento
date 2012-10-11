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

class Unirgy_RapidFlowPro_Block_Adminhtml_Profile_Eav_ImportOptions
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function _prepareForm()
    {
        $hlp = Mage::helper('urapidflow');
        $source = Mage::getSingleton('urapidflow/source');

        $profile = Mage::registry('profile_data');

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('import_options_form', array('legend'=>$this->__('Import Options')));

        $fieldset->addField('store_ids', 'multiselect', array(
            'label'     => $this->__('Limit Stores to Import'),
            'name'      => 'options[store_ids]',
            'values'    => $source->setPath('stores')->toOptionArray(),
            'value'     => $profile->getData('options/store_ids'),
            'note'      => $this->__('wherever applicable'),
        ));

        $fieldset->addField('import_row_types', 'multiselect', array(
            'label'     => $this->__('Limit Row Types to Import'),
            'name'      => 'options[row_types]',
            'values'    => $source->setDataType($profile->getDataType())
                ->setPath('row_type')->toOptionArray(),
            'value'     => $profile->getData('options/row_types'),
        ));

        $fieldset->addField('export_duplicate_option_values', 'select', array(
             'label'     => $this->__('Allow duplicate option values [EAO]'),
             'name'      => 'options[duplicate_option_values]',
             'values'    => $source->setPath('yesno')->toOptionArray(),
             'value'     => $profile->getData('options/duplicate_option_values'),
        ));
        
        if (Mage::helper('urapidflow')->hasMageFeature('indexer_1.4')) {
            $fieldset->addField('import_reindex_type', 'select', array(
                'label'     => $this->__('Reindex type'),
                'name'      => 'options[import][reindex_type]',
                'values'    => $source->setPath('import_reindex_type_nort')->toOptionArray(),
                'value'     => $profile->getData('options/import/reindex_type'),
            ));
        }

        return parent::_prepareForm();
    }
}