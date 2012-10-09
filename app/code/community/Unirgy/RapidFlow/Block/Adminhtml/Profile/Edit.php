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

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'urapidflow';
        $this->_controller = 'adminhtml_profile';

        $hlp = Mage::helper('urapidflow');
        $profile = Mage::registry('profile_data');
        $id = $profile->getId();

        if (Mage::getStoreConfig('urapidflow/advanced/disable_changes')) {
            $this->_removeButton('reset');
            $this->_removeButton('save');
            $this->_removeButton('delete');
            return;
        }

        switch ($profile->getRunStatus()) {
        case 'pending': case 'running': case 'paused':
            //$this->_removeButton('back');
            $this->_removeButton('reset');
            $this->_removeButton('save');
            $this->_removeButton('delete');

            if (false && $profile->getInvokeStatus()!=='foreground') {
                if ($profile->getRunStatus()=='paused') {
                    $this->_addButton('resume', array(
                        'label'     => $this->__('Resume'),
                        'onclick'   => "location.href = '".$this->getUrl('urapidflowadmin/adminhtml_profile/resume', array('id'=>$id))."'",
                    ), 0);
                } else {
                    $this->_addButton('pause', array(
                        'label'     => $this->__('Pause'),
                        'onclick'   => "location.href = '".$this->getUrl('urapidflowadmin/adminhtml_profile/pause', array('id'=>$id))."'",
                    ), 0);
                }
            }

            $this->_addButton('stop', array(
                'label'     => $this->__('Stop'),
                'onclick'   => "location.href = '".$this->getUrl('urapidflowadmin/adminhtml_profile/stop', array('id'=>$id))."'",
                'class'     => 'delete',
            ), 0);
            break;

        default:
            $this->_addButton('saveandcontinue', array(
                'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
                'onclick'   => "editForm.submit(\$('edit_form').action+'back/edit/')",
                'class'     => 'save',
            ), -100);

            if ($id) {
                /*
                $this->_addButton('start_fg', array(
                    'label'     => $this->__('Run Foreground'),
                    'onclick'   => "editForm.submit(\$('edit_form').action+'start/foreground/back/edit/')",
                    'class'     => 'save',
                ), 0);
                */
                $this->_addButton('start_bg', array(
                    'label'     => $this->__('Save And Run'),
                    'onclick'   => "editForm.submit(\$('edit_form').action+'start/ondemand/back/edit/')",
                    'class'     => 'save',
                ), 0);
            }
        }
    }

    public function getHeaderText()
    {
        $profile = Mage::registry('profile_data');
        $hlp = Mage::helper('urapidflow');

        if ($profile && $profile->getId()) {
            $title = $this->htmlEscape($profile->getTitle());
            switch ($profile->getRunStatus()) {
            case 'pending': case 'running':
                $title = $this->__("Running Profile State '%s'", $title);
                break;

            case 'paused':
                $title = $this->__("Paused Profile State '%s'", $title);
                break;

            default:
                $title = $this->__("Edit Profile '%s'", $title);
            }
            return $title;
        } else {
            return $this->__('Add Profile');
        }
    }
}
