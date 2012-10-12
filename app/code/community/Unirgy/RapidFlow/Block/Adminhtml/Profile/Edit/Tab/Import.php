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

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit_Tab_Import extends Mage_Adminhtml_Block_Widget_Form
{
    public function _prepareForm()
    {
        $hlp = Mage::helper('urapidflow');
        $source = Mage::getSingleton('urapidflow/source');

        $profile = Mage::registry('profile_data');

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('import_options_form', array('legend'=>$this->__('Import Options')));

        $fieldset->addField('import_actions', 'select', array(
            'label'     => $this->__('Allowed Import Actions'),
            'name'      => 'options[import][actions]',
            'values'    => $source->setPath('import_actions')->toOptionArray(),
            'value'     => $profile->getData('options/import/actions'),
        ));

        $fieldset->addField('import_dryrun', 'select', array(
            'label'     => $this->__('Dry Run (validate data only)'),
            'name'      => 'options[import][dryrun]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/dryrun'),
        ));

        $fieldset->addField('import_change_typeset', 'select', array(
            'label'     => $this->__('Allow changing product type and attribute set for existing products'),
            'name'      => 'options[import][change_typeset]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/change_typeset'),
        ));

        $fieldset->addField('import_select_ids', 'select', array(
            'label'     => $this->__('Allow internal values for dropdown attributes'),
            'name'      => 'options[import][select_ids]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/select_ids'),
        ));

        $fieldset->addField('import_not_applicable', 'select', array(
            'label'     => $this->__('Allow importing values for not applicable attributes'),
            'name'      => 'options[import][not_applicable]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/not_applicable'),
        ));

        $fieldset->addField('import_same_as_default', 'select', array(
            'label'     => $this->__('If store values the same as default'),
            'name'      => 'options[import][store_value_same_as_default]',
            'values'    => $source->setPath('store_value_same_as_default')->toOptionArray(),
            'value'     => $profile->getData('options/import/store_value_same_as_default'),
            'comment'   => $this->__('Affects only updated values'),
        ));

        $fieldset->addField('import_stock_zero_out', 'select', array(
            'label'     => $this->__('If stock qty is 0, mark product as Out of stock'),
            'name'      => 'options[import][stock_zero_out]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/stock_zero_out'),
        ));

        if (Mage::helper('urapidflow')->hasMageFeature('indexer_1.4')) {
            $fieldset->addField('import_reindex_type', 'select', array(
                'label'     => $this->__('Reindex type'),
                'name'      => 'options[import][reindex_type]',
                'values'    => $source->setPath('import_reindex_type')->toOptionArray(),
                'value'     => $profile->getData('options/import/reindex_type'),
            ));
        }

        $fieldset = $form->addFieldset('import_images', array('legend'=>$this->__('Image Options')));

        $fieldset->addField('import_image_files', 'select', array(
            'label'     => $this->__('Auto-import image files'),
            'name'      => 'options[import][image_files]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/image_files'),
        ));

        $fieldset->addField('import_image_files_remote', 'select', array(
            'label'     => $this->__('Download remote HTTP images'),
            'name'      => 'options[import][image_files_remote]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/image_files_remote'),
            'note'      => $this->__('Might not work for dynamically generated remote images'),
        ));

        $fieldset->addField('import_image_remote_subfolder_level', 'select', array(
            'label'     => $this->__('Retain remote subfolders'),
            'name'      => 'options[import][image_remote_subfolder_level]',
            'values'    => $source->setPath('import_image_remote_subfolder_level')->toOptionArray(),
            'value'     => $profile->getData('options/import/image_remote_subfolder_level'),
        ));

        $fieldset->addField('import_image_delete_old', 'select', array(
            'label'     => $this->__('Delete old image'),
            'name'      => 'options[import][image_delete_old]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/image_delete_old'),
            'note'      => $this->__('Old image will be deleted from filesystem only if not used by other products or "Skip usage check when delete" = "Yes""'),
        ));

        $fieldset->addField('import_image_delete_skip_usage_check', 'select', array(
            'label'     => $this->__('Skip usage check when delete old image'),
            'name'      => 'options[import][image_delete_skip_usage_check]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/image_delete_skip_usage_check'),
            'note'      => $this->__('Setting this option will skip check for usage of image to delete by other products'),
        ));

        $fieldset->addField('import_image_missing_file', 'select', array(
            'label'     => $this->__('Action on missing image file'),
            'name'      => 'options[import][image_missing_file]',
            'values'    => $source->setPath('import_image_missing_file')->toOptionArray(),
            'value'     => $profile->getData('options/import/image_missing_file'),
        ));

        $fieldset->addField('import_image_source_dir', 'text', array(
            'label'     => $this->__('Local Source Folder'),
            'name'      => 'options[dir][images]',
            'value'     => $profile->getData('options/dir/images'),
            'note'      => $this->__('If empty, global configuration will be used'),
        ));

        $fieldset = $form->addFieldset('import_autocreate_options_form', array('legend'=>$this->__('Auto-Create Missing Attribute Option Values')));

        $fieldset->addField('import_create_options', 'select', array(
            'label'     => $this->__('Enable'),
            'name'      => 'options[import][create_options]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/create_options'),
        ));

        $fieldset = $form->addFieldset('import_autocreate_categories_form', array('legend'=>$this->__('Auto-Create Categories (category.name column only)')));

        $fieldset->addField('import_create_categories', 'select', array(
            'label'     => $this->__('Enable'),
            'name'      => 'options[import][create_categories]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/create_categories'),
        ));

        $fieldset->addField('import_create_categories_active', 'select', array(
            'label'     => $this->__('Default Active?'),
            'name'      => 'options[import][create_categories_active]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/create_categories_active'),
        ));

        $fieldset->addField('import_create_categories_anchor', 'select', array(
            'label'     => $this->__('Default Anchored?'),
            'name'      => 'options[import][create_categories_anchor]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/create_categories_anchor'),
        ));

        $fieldset->addField('import_create_categories_display', 'select', array(
            'label'     => $this->__('Default Display Mode'),
            'name'      => 'options[import][create_categories_display]',
            'values'    => $source->setPath('category_display_mode')->toOptionArray(),
            'value'     => $profile->getData('options/import/create_categories_display'),
        ));


        if (Mage::helper('urapidflow')->hasMageFeature('category.include_in_menu')) {
            $fieldset->addField('import_create_categories_menu', 'select', array(
                'label'     => $this->__('Default Include In Menu?'),
                'name'      => 'options[import][create_categories_menu]',
                'values'    => $source->setPath('yesno')->toOptionArray(),
                'value'     => $profile->getData('options/import/create_categories_menu'),
            ));
        }

        $fieldset->addField('import_delete_old_category_products', 'select', array(
            'label'     => $this->__('Delete old category-product associations'),
            'name'      => 'options[import][delete_old_category_products]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/delete_old_category_products'),
        ));

        $fieldset = $form->addFieldset('import_autocreate_attributeset_form', array('legend'=>$this->__('Auto-Create Attribute Sets')));

        $fieldset->addField('import_create_attributesets', 'select', array(
            'label'     => $this->__('Enable'),
            'name'      => 'options[import][create_attributesets]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/import/create_attributesets'),
        ));


        $fieldset->addField('import_create_attributeset_template', 'select', array(
            'label'     => $this->__('Auto-created Attribute Set Template'),
            'name'      => 'options[import][create_attributeset_template]',
            'values'    => $source->setPath('attribute_sets')->toOptionArray(),
            'value'     => $profile->getData('options/import/create_attributeset_template'),
        ));

        $fieldset = $form->addFieldset('import_advanced_form', array('legend'=>$this->__('Advanced Settings')));

/*
        $fieldset->addField('import_save_attributes_method', 'select', array(
            'label'     => $this->__('Save Attributes Method'),
            'name'      => 'options[import][save_attributes_method]',
            'values'    => $source->setPath('save_attributes_method')->toOptionArray(),
            'value'     => $profile->getData('options/import/save_attributes_method'),
            'note'   => $this->__('Use PDOStatement for long text attributes (>10KB)<br/>PDOStatement method might not work with some PHP versions (5.2.6)'),
        ));
*/

        $fieldset->addField('import_insert_attr_chunk_size', 'text', array(
            'label'     => $this->__('Insert Attribute Values Chunk Size'),
            'name'      => 'options[import][insert_attr_chunk_size]',
            'value'     => $profile->getData('options/import/insert_attr_chunk_size'),
            'note'   => $this->__('Number of attribute value records to insert at the same time.Default value is 100. If there are large text values, use small number (1), but it might affect inserting new attribute values performance.'),
        ));

        return parent::_prepareForm();
    }
}