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
 
class Harapartners_Categoryevent_Block_Adminhtml_Sort_Index_Edit
    extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('categoryevent/sort/index/edit.phtml');
    }

    public function getSortSavePostUrl()
    {
        return $this->getUrl('categoryevent/adminhtml_sort/sortsave');
    }

    public function getSortRebuildPostUrl()
    {
        return $this->getUrl('categoryevent/adminhtml_sort/sortrebuild');
    }

    public function getAutoSortPostUrl()
    {
        return $this->getUrl('categoryevent/adminhtml_sort/sortByDate');
    }

    public function getEventUrl($event)
    {
        return $this->getUrl('adminhtml/catalog_category/edit',
            array(
                'store' => $this->getRequest()->getParam('store'),
                'id'    => $event['entity_id']
            )
        );
    }

    public function getSortentry()
    {
        $sortDate = $this->getRequest()->getParam('sort_date');

        if ($sortentry = Mage::getSingleton('adminhtml/session')->getData("sortentry_$sortDate")) {
            return $sortentry;
        }

        $sortentry = Mage::getModel('categoryevent/sortentry')->loadByDate($sortDate);

        Mage::getSingleton('adminhtml/session')->setData("sortentry_$sortDate", $sortentry);
        return $sortentry;
    }
}
