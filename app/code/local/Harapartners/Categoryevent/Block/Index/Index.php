<?php
/**
 * Harapartners
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Harapartners License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.Harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Harapartners.com so we can send you a copy immediately.
 *
 * 
 */

class Harapartners_Categoryevent_Block_Index_Index
    extends Mage_Core_Block_Template
{
    public function getIndexDataObject()
    {
        return Mage::getModel('categoryevent/sortentry')
            ->loadCurrent()
            ->adjustQueuesForCurrentTime();
    }

    /**
     * Get the number of products associated with a category/event.
     *
     * @param int $categoryId
     *
     * @return int
     */
    public function countCategoryProducts($categoryId)
    {
        /** @var $read Varien_Db_Adapter_Interface */
        $read   = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $read->select()
            ->from('catalog_category_product', 'count(*)')
            ->where('category_id = ?', $categoryId);

        return $read->fetchOne($select);
    }
}
