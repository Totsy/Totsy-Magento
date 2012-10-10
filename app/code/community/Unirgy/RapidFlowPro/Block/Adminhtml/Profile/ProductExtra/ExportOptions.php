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

class Unirgy_RapidFlowPro_Block_Adminhtml_Profile_ProductExtra_ExportOptions
    extends Mage_Adminhtml_Block_Widget_Form
{
    public function _prepareForm()
    {
        $hlp = Mage::helper('urapidflow');
        $source = Mage::getSingleton('urapidflow/source');

        $profile = Mage::registry('profile_data');

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('export_options_form', array('legend'=>$this->__('Export Options')));

        $fieldset->addField('store_ids', 'multiselect', array(
            'label'     => $this->__('Stores to Export'),
            'name'      => 'options[store_ids]',
            'values'    => $source->setPath('stores')->toOptionArray(),
            'value'     => $profile->getData('options/store_ids'),
        ));

        $fieldset->addField('export_row_types', 'multiselect', array(
            'label'     => $this->__('Row Types'),
            'name'      => 'options[row_types]',
            'values'    => $source->setDataType($profile->getDataType())->setStripFromLabel('/^Catalog Product/')
                ->setPath('row_type')->toOptionArray(),
            'value'     => $profile->getData('options/row_types'),
        ));

        $fieldset->addField('export_image_files', 'select', array(
            'label'     => $this->__('Auto-export image files'),
            'name'      => 'options[export][image_files]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => $profile->getData('options/export/image_files'),
        ));

        return parent::_prepareForm();
    }
}