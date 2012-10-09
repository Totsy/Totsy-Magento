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

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('profile_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Profile Information'));
    }

    protected function _beforeToHtml()
    {
        $hlp = Mage::helper('urapidflow');
        $profile = Mage::registry('profile_data');

        if (in_array($profile->getRunStatus(), array('pending', 'running', 'paused'))) {
            $this->addTab('status_section', array(
                'label'     => $this->__('Profile Status'),
                'title'     => $this->__('Profile Status'),
                'content'   => $this->getLayout()->createBlock('urapidflow/adminhtml_profile_edit_tab_status')
                    ->setProfile($profile)
                    ->toHtml(),
            ));
            return parent::_beforeToHtml();
        }

        $this->addTab('main_section', array(
            'label'     => $this->__('Profile Information'),
            'title'     => $this->__('Profile Information'),
            'content'   => $this->getLayout()->createBlock('urapidflow/adminhtml_profile_edit_tab_main')
                ->setProfile($profile)
                ->toHtml(),
        ));

        $jsonTab = array(
            'label'     => $this->__('Profile Configuration as JSON'),
            'title'     => $this->__('Profile Configuration as JSON'),
            'content'   => $this->getLayout()->createBlock('urapidflow/adminhtml_profile_edit_tab_json')
                ->setProfile($profile)
                ->toHtml(),
        );

        if (!$profile->getId()) {
            $this->addTab('json_section', $jsonTab);
            return parent::_beforeToHtml();
        }

        if (in_array($profile->getRunStatus(), array('stopped', 'finished'))) {
            $this->addTab('status_section', array(
                'label'     => $this->__('Profile Status'),
                'title'     => $this->__('Profile Status'),
                'content'   => $this->getLayout()->createBlock('urapidflow/adminhtml_profile_status')
                    ->setProfile($profile)
                    ->toHtml(),
            ));
        }
/*
        $this->addTab('schedule_section', array(
            'label'     => $this->__('Schedule Options'),
            'title'     => $this->__('Schedule Options'),
            'content'   => $this->getLayout()->createBlock('urapidflow/adminhtml_profile_edit_tab_schedule')
                ->setProfile($profile)
                ->toHtml(),
        ));
*/
        $tabs = Mage::getSingleton('urapidflow/config')
            ->getProfileTabs($profile->getProfileType(), $profile->getDataType());

        if ($tabs) {
            foreach ($tabs as $key=>$tab) {
                $this->addTab($key.'_section', array(
                    'label'     => $this->__((string)$tab->title),
                    'title'     => $this->__((string)$tab->title),
                    'content'   => $this->getLayout()->createBlock((string)$tab->block)
                        ->setProfile($profile)
                        ->toHtml(),
                ));
            }
        }

        $this->addTab('json_section', $jsonTab);

        return parent::_beforeToHtml();
    }
}