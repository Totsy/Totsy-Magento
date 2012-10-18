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

class Harapartners_Service_Model_Rewrite_Catalog_Product extends Mage_Catalog_Model_Product {

    //Preview related functions
    public function getProductUrl($useSid = null)
    {
        if (Mage::registry('admin_preview_mode') && Mage::registry('current_category')) {
            $targetPath = 'catalog/product/preview/category/' .
                Mage::registry('current_category')->getId() .
                '/id/' .
                $this->getId();
            $pageKey = base64_encode(Mage::helper('core')->encrypt($targetPath));
            return Mage::getUrl($targetPath, array('page_key' => $pageKey));
        } else {
            return parent::getProductUrl($useSid);
        }
    }

    //Product out of the live event is NOT salable
    public function isSalable()
    {
        return (
            Mage::registry('current_category') ||
            (false !== $this->getLiveCategory()) ||
            (null !== $this->getPromoRule())
        ) && parent::isSalable();
    }

    public function cleanCache()
    {
        return Mage::registry('batch_import_no_index') ? $this : parent::cleanCache();
    }

    public function afterCommitCallback()
    {
        // ===== Index rebuild ========================================== //

        //Create catalog-inventory index only upon product creation!
        if (!$this->getOrigData('entity_id')) {
            Mage::getResourceSingleton('cataloginventory/indexer_stock')->reindexProducts(array($this->getId()));
        }

        if (Mage::registry('batch_import_no_index')) {
            Mage::dispatchEvent('model_save_commit_after', array('object' => $this));
            Mage::dispatchEvent($this->_eventPrefix . '_save_commit_after', $this->_getEventData());

            return $this;
        } else {
            //Note URL rewrite needs to be refreshed separately, if included within default index, it is much slower
            //Invisible simple product has the same name as configurable product
            //Thus when indexing url, the conf product would be "{{url_key}}-{{addtional_number_to_avoid_conflict}}.html"
            if ($this->getVisibility() != Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
                $urlModel = Mage::getSingleton('catalog/url');
                $urlModel->refreshProductRewrite($this->getId()); //Category path also included
            }

            return parent::afterCommitCallback();
        }
    }

    //Important logic for legacy order import
    public function getFinalPrice($qty=null)
    {
        if (Mage::registry('order_import_force_product_price') &&
            $this->getOrderImportFinalPrice()
        ) {
            return max(array(0.0, $this->getOrderImportFinalPrice()));
        }

        return parent::getFinalPrice($qty);
    }

    /**
     * Get attribute label text, for a given store.
     *
     * @param string $attributeCode
     * @param int    $storeId
     *
     * @return string
     */
    public function getAttributeTextByStore($attributeCode, $storeId)
    {
        return $this->getResource()
            ->getAttribute($attributeCode)
            ->setStoreId($storeId)
            ->getSource()
            ->getOptionText($this->getData($attributeCode));
    }

    /**
     * Get the live category associated with this product, or FALSE if it is not
     * associated with a live event.
     *
     * @return Mage_Catalog_Model_Category|false
     */
    public function getLiveCategory()
    {
        $now        = Mage::getModel('core/date')->timestamp();
        $categories = $this->getCategoryCollection()
            ->addAttributeToSelect(array('event_start_date','event_end_date'));

        foreach ($categories as $category) {
            if (strtotime($category['event_start_date']) < $now &&
                strtotime($category['event_end_date']) > $now
            ) {
                return $category;
            }
        }

        return false;
    }

    /**
     * Get a promotion rule that uses this product, if one exists.
     *
     * @return Mage_Salesrule_Model_Rule|null
     */
    public function getPromoRule()
    {
        return Mage::getModel('salesrule/rule')->getCollection()
            ->addFieldToFilter('promo_sku', $this->getSku())
            ->getFirstItem();
    }
}
