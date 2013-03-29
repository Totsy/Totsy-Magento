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
 
class Harapartners_Categoryevent_Helper_Data extends Mage_Core_Helper_Abstract{    
    public function getLiveCategories($storeId) {
        $eventParentCategory = Mage::getModel('categoryevent/sortentry')->getParentCategory(Harapartners_Categoryevent_Model_Sortentry::EVENT_CATEGORY_NAME, $storeId);
        if (!$eventParentCategory) {
            return $this;
        }
        $date = date("Y-m-d",Mage::getSingleton('core/date')->timestamp() + (60 * 60 * 24));
        $endDate = date("Y-m-d",Mage::getSingleton('core/date')->timestamp());
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToFilter('parent_id', $eventParentCategory->getId())
            ->addAttributeToFilter('level', Harapartners_Categoryevent_Model_Sortentry::CATEGORYEVENT_LEVEL)
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter('event_start_date', array('to' => $date, 'date' => true ))
            ->addAttributeToFilter('event_end_date', array('from' => $endDate, 'date' => true ));
        return $collection;
    }
}