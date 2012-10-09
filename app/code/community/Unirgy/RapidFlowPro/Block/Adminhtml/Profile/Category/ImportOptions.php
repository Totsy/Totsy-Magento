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

class Unirgy_RapidFlowPro_Block_Adminhtml_Profile_Category_ImportOptions
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function _prepareForm()
    {
        $hlp = Mage::helper('urapidflow');
        $source = Mage::getSingleton('urapidflow/source');

        $profile = Mage::registry('profile_data');

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('import_options_form', array('legend'=>Mage::helper('urapidflow')->__('Import Options')));

        $fieldset->addField('import_actions', 'select', array(
            'label'     => Mage::helper('urapidflow')->__('Allowed Import Actions'),
            'name'      => 'options[import][actions]',
            'values'    => $source->setPath('import_actions')->toOptionArray(),
            'value'     => $profile->getData('options/import/actions'),
        ));

        $fieldset->addField('import_dryrun', 'select', array(
            'label'     => Mage::helper('urapidflow')->__('Dry Run (validate data only)'),
            'name'      => 'options[import][dryrun]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/dryrun'),
        ));

        $fieldset->addField('import_select_ids', 'select', array(
            'label'     => Mage::helper('urapidflow')->__('Allow internal values for dropdown attributes'),
            'name'      => 'options[import][select_ids]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/select_ids'),
        ));

        $fieldset->addField('import_same_as_default', 'select', array(
            'label'     => Mage::helper('urapidflow')->__('If store values the same as default'),
            'name'      => 'options[import][store_value_same_as_default]',
            'values'    => $source->setPath('store_value_same_as_default')->toOptionArray(),
            'value'     => $profile->getData('options/import/store_value_same_as_default'),
            'comment'   => Mage::helper('urapidflow')->__('Affects only updated values'),
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