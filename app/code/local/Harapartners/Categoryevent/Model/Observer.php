<?php
/**
 * @category    Totsy
 * @package     Harapartners_Categoryevent_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Harapartners_Categoryevent_Model_Observer
{
    /**
     * Maintain category/events.
     */
    public function maintainCategoryEvents()
    {
        $eventCategory = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToFilter('name', Harapartners_Categoryevent_Model_Sortentry::EVENT_CATEGORY_NAME)
            ->getFirstItem();

        $expiredEventCategory = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToFilter('name', Harapartners_Categoryevent_Model_Sortentry::EVENT_EXPIRED_CATEGORY_NAME)
            ->getFirstItem();

        // find any categories that are not in level 3, besides the two reserved
        // events (Live and Expired)
        $reservedNames = array(
            'Root Catalog',
            'Totsy',
            Harapartners_Categoryevent_Model_Sortentry::TOP_EVENT_CATEGORY_NAME,
            Harapartners_Categoryevent_Model_Sortentry::EVENT_EXPIRED_CATEGORY_NAME,
            Harapartners_Categoryevent_Model_Sortentry::EVENT_CATEGORY_NAME,
        );

        $categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToFilter('level', array('neq' => 3))
            ->addAttributeToFilter('name', array('nin' => $reservedNames));

        // move each event found in the Live Events parent category
        foreach ($categories as $category) {
            $category->move($eventCategory->getId(), null);
        }

        $now = Mage::getModel('core/date')->date();
        $categories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToFilter('parent_id', $eventCategory->getId())
            ->addAttributeToFilter('event_end_date', array('to' => $now, 'datetime' => true));

        // move each past event in the Live Events parent category into the
        // Expired Events parent category
        foreach ($categories as $category) {
            $category->move($expiredEventCategory->getId(), null);
        }
    }
}
