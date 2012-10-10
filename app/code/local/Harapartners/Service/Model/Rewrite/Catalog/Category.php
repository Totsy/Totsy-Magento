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

class Harapartners_Service_Model_Rewrite_Catalog_Category
    extends Mage_Catalog_Model_Category
{
    /**
     * Harapartners, Jun, Event and Top Event are immutable for Totsy logic
     *
     * @param int  $parentId
     * @param int  $afterCategoryId
     * @param bool $reIndex
     *
     * @return Mage_Catalog_Model_Category
     */
    public function move($parentId, $afterCategoryId, $reIndex = false)
    {
        $this->_totsyReserveAnchorCategoryCheck($parentId);

        // Moving categories will trigger url re-index, which is very slow for
        // large categories, ignore by default
        if (!$reIndex) {
            Mage::getSingleton('index/indexer')->lockIndexer();
        }

        return parent::move($parentId, $afterCategoryId);
    }

    protected function _beforeSave()
    {
        $this->_totsyReserveAnchorCategoryCheck();
        return parent::_beforeSave();
    }

    protected function _beforeDelete()
    {
        $this->_totsyReserveAnchorCategoryCheck();
        return parent::_beforeDelete();
    }

    protected function _totsyReserveAnchorCategoryCheck($parentId = null)
    {
        if ($this->getData('name') == Harapartners_Categoryevent_Model_Sortentry::EVENT_CATEGORY_NAME ||
            $this->getOrigData('name') == Harapartners_Categoryevent_Model_Sortentry::EVENT_CATEGORY_NAME ||
            $this->getData('name') == Harapartners_Categoryevent_Model_Sortentry::TOP_EVENT_CATEGORY_NAME ||
            $this->getOrigData('name') == Harapartners_Categoryevent_Model_Sortentry::TOP_EVENT_CATEGORY_NAME
        ) {
            Mage::throwException('This event is a fixed/reserve event that cannot be modified.');
        }

        if (null != $parentId) {
            $eventCategory = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToFilter('name', Harapartners_Categoryevent_Model_Sortentry::EVENT_CATEGORY_NAME)
                ->getFirstItem();

            $expiredEventCategory = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToFilter('name', Harapartners_Categoryevent_Model_Sortentry::EVENT_EXPIRED_CATEGORY_NAME)
                ->getFirstItem();

            if ($parentId != $eventCategory->getId() && $parentId != $expiredEventCategory->getId()) {
                Mage::throwException('Events can only be moved into the fixed/reserve events (Live and Expired)');
            }
        }

        return $this;
    }

}
