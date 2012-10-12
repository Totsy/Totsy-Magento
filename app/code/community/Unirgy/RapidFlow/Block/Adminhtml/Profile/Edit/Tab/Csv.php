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

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit_Tab_Csv extends Mage_Adminhtml_Block_Widget_Form
{
    public function _prepareForm()
    {
        $hlp = Mage::helper('urapidflow');
        $source = Mage::getSingleton('urapidflow/source');

        $profile = Mage::registry('profile_data');

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('csv_form', array('legend'=>$this->__('CSV Options')));

        $encodings = $source->setPath('encoding')->toOptionArray();
        if ($profile->getProfileType()=='import') {
            $fieldset->addField('encoding_from', 'select', array(
                'label'     => $this->__('File Encoding'),
                'name'      => 'options[encoding][from]',
                'value'     => $profile->getData('options/encoding/from'),
                'values'    => $encodings,
            ));
        } else {
            unset($encodings['auto']);
            $fieldset->addField('encoding_to', 'select', array(
                'label'     => $this->__('File Encoding'),
                'name'      => 'options[encoding][to]',
                'value'     => $profile->getData('options/encoding/to'),
                'values'    => $encodings,
            ));
        }

        $fieldset->addField('encoding_illegal_char', 'select', array(
            'label'     => $this->__('Action to take on illegal character during conversion'),
            'name'      => 'options[encoding][illegal_char]',
            'values'    => $source->setPath('encoding_illegal_char')->toOptionArray(),
            'value'     => $profile->getData('options/encoding/illegal_char'),
        ));

        $fieldset->addField('csv_delimiter', 'text', array(
            'label'     => $this->__('Field Delimiter'),
            'required'  => true,
            'class'     => 'required-entry',
            'name'      => 'options[csv][delimiter]',
            'value'     => $profile->getData('options/csv/delimiter'),
        ));

        $fieldset->addField('csv_enclosure', 'text', array(
            'label'     => $this->__('Field Enclosure'),
            'required'  => true,
            'class'     => 'required-entry',
            'name'      => 'options[csv][enclosure]',
            'value'     => $profile->getData('options/csv/enclosure'),
        ));
/*
        $fieldset->addField('csv_escape', 'text', array(
            'label'     => $hlp->__('Quote Escape'),
            'required'  => true,
            'class'     => 'required-entry',
            'name'      => 'options[csv][escape]',
            'value'     => $profile->getData('options/csv/escape'),
        ));
*/
        $fieldset->addField('csv_multivalue_separator', 'text', array(
            'label'     => $this->__('Default Multivalue Separator'),
            'required'  => true,
            'class'     => 'required-entry',
            'name'      => 'options[csv][multivalue_separator]',
            'value'     => $profile->getData('options/csv/multivalue_separator'),
        ));

        return parent::_prepareForm();
    }
}