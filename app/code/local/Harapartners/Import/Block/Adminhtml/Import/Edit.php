<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Import_Block_Adminhtml_Import_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'import';
        $this->_controller = 'adminhtml_import';
        
        $this->_updateButton('save', 'label', Mage::helper('import')->__('Import'));
        $this->_updateButton('delete', 'label', Mage::helper('import')->__('Cancel'));
		
        /*
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

		*/
        
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('import_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'import_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'import_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('import_data') && Mage::registry('import_data')->getId() ) {
            return Mage::helper('import')->__("Edit Import '%s'", $this->htmlEscape(Mage::registry('import_data')->getTitle()));
        } else {
            return Mage::helper('import')->__('Upload Import FIle');
        }
    }
}