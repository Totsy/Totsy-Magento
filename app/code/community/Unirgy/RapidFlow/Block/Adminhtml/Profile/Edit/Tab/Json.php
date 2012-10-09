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

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit_Tab_Json extends Mage_Adminhtml_Block_Widget_Form
{
    public function _prepareForm()
    {
        $hlp = Mage::helper('urapidflow');
        $source = Mage::getSingleton('urapidflow/source');

        $profile = Mage::registry('profile_data');

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('json_form', array('legend'=>$this->__('Profile Configuration')));

        if (!$profile || !$profile->getId()) {
            $fieldset->addField('json_import', 'textarea', array(
                'label'     => $this->__('Import Profile Configuration'),
                'name'      => 'json_import',
                'style'     => 'width:500px; height:500px; font-family:Courier New;',
            ));
        } else {
            $fieldset->addField('json_export', 'textarea', array(
                'label'     => $this->__('Export Profile Configuration'),
                'name'      => 'json_export',
                'readonly'  => true,
                'value'     => $profile->exportToJSON(),
                'style'     => 'width:500px; height:500px; font-family:Courier New;',
            ));
        }

        return parent::_prepareForm();
    }

    public function indent($json) {


    }
}