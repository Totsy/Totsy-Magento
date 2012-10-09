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

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit_Tab_File extends Mage_Adminhtml_Block_Widget_Form
{
    public function _prepareForm()
    {
        $hlp = Mage::helper('urapidflow');
        $source = Mage::getSingleton('urapidflow/source');

        $profile = Mage::registry('profile_data');

        $form = new Varien_Data_Form();
        $this->setForm($form);

/*
        if ($profile->getDataType()=='product') {

            $fieldset = $form->addFieldset('dirs_form', array('legend'=>$this->__('Target Directories')));

            $fieldset->addField('import_images_dir', 'text', array(
                'label'     => $this->__('Images'),
                'name'      => 'options[dir][images]',
                'value'     => $profile->getData('options/dir/images'),
                'note'      => $hlp->__('Leave empty for default'),
            ));
            $fieldset->addField('import_downloads_dir', 'text', array(
                'label'     => $this->__('Downloadables'),
                'name'      => 'options[dir][downloads]',
                'value'     => $profile->getData('options/dir/downloads'),
                'note'      => $hlp->__('Leave empty for default'),
            ));
        }
*/


        $fieldset = $form->addFieldset('remote_options_form', array('legend'=>$this->__('Remote Server')));

        $fieldset->addField('remote_type', 'select', array(
            'label'     => $this->__('Server Type'),
            'name'      => 'options[remote][type]',
            'values'    => $source->setPath('remote_type')->toOptionArray(),
            'value'     => $profile->getData('options/remote/type'),
        ));

        $fieldset->addField('remote_host', 'text', array(
            'label'     => $this->__('Host'),
            'name'      => 'options[remote][host]',
            'value'     => $profile->getData('options/remote/host'),
        ));

        $fieldset->addField('remote_port', 'text', array(
            'label'     => $this->__('Port'),
            'name'      => 'options[remote][port]',
            'value'     => $profile->getData('options/remote/port'),
            'note'      => $hlp->__('Leave empty for default'),
        ));

        $fieldset->addField('remote_username', 'text', array(
            'label'     => $this->__('User Name'),
            'name'      => 'options[remote][username]',
            'value'     => $profile->getData('options/remote/username'),
        ));

        $fieldset->addField('remote_password', 'text', array(
            'label'     => $this->__('Password'),
            'name'      => 'options[remote][password]',
            'value'     => $profile->getData('options/remote/password'),
        ));

        $fieldset->addField('remote_path', 'text', array(
            'label'     => $this->__('Path (Folder)'),
            'name'      => 'options[remote][path]',
            'value'     => $profile->getData('options/remote/path'),
        ));

        $ftpPassive = $profile->getData('options/remote/ftp_passive');
        $fieldset->addField('ftp_passive', 'select', array(
            'label'     => $this->__('Ftp Passive Mode'),
            'name'      => 'options[remote][ftp_passive]',
            'values'    => $source->setPath('yesno')->toOptionArray(),
            'value'     => is_null($ftpPassive) ? 1 : $ftpPassive,
        ));

        $ftpMode = $profile->getData('options/remote/ftp_file_mode');
        $fieldset->addField('ftp_file_mode', 'select', array(
            'label'     => $this->__('Ftp File Mode'),
            'name'      => 'options[remote][ftp_file_mode]',
            'values'    => $source->setPath('ftp_file_mode')->toOptionArray(),
            'value'     => is_null($ftpMode) ? FTP_BINARY : $ftpMode,
        ));

/*
        $fieldset = $form->addFieldset('compress_optionsform', array('legend'=>$this->__('Compression')));

        $fieldset->addField('compress_type', 'select', array(
            'label'     => $this->__('Compression Type'),
            'name'      => 'options[compress][type]',
            'values'    => $source->setPath('compress_type')->toOptionArray(),
            'value'     => $profile->getData('options/compress/type'),
        ));
*/
        return parent::_prepareForm();
    }
}