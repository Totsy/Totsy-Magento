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

class Harapartners_Service_Helper_Rewrite_Catalog_Product
    extends Mage_Catalog_Helper_Product
{
    public function initProduct($productId, $controller, $params = null)
    {
        // Prepare data for routine
        if (!$params) {
            $params = new Varien_Object();
        }

        /* SKIP THIS INIT FOR NOW . Slav September 27 2012
        // Init and load product
        Mage::dispatchEvent('catalog_controller_product_init_before', array(
            'controller_action' => $controller,
            'params' => $params,
        ));
        */
        if (!$productId) {
            return false;
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);

        if (!$this->canShow($product)) {
            return false;
        }
        if (!in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
            return false;
        }

        // Each product must have a category/event
        $categoryId = $params->getCategoryId();
        if (!$categoryId) {
            $categoryEventId = $this->getLiveCategoryIdFromCategoryEventSort($product);
            if ($product->canBeShowInCategory($categoryEventId)) {
                $categoryId = $categoryEventId;
            }
        }

        if (!$categoryId) {
            return false;
        }
        
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $product->setCategory($category);
        Mage::register('current_category', $category);

        // Register current data and dispatch final events
        Mage::register('current_product', $product);
        Mage::register('product', $product);

        try {
            Mage::dispatchEvent('catalog_controller_product_init', array('product' => $product));
            Mage::dispatchEvent('catalog_controller_product_init_after',
                            array('product' => $product,
                                'controller_action' => $controller
                            )
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }
        return $product;
    }

    /**
     * Search for the latest matching live event.
     *
     * @param $product
     * @return null|int
     */
    public function getLiveCategoryIdFromCategoryEventSort($product)
    {
        $categoryIds = $product->getCategoryIds();
        $sortentry = Mage::getModel('categoryevent/sortentry')->loadCurrent();
        $liveEvents = json_decode($sortentry->getLiveQueue(), true);
        foreach ($liveEvents as $event){
            if (isset($event['entity_id']) &&
                in_array($event['entity_id'], $categoryIds)
            ) {
                return $event['entity_id'];
            }
        }

        return null;
    }
}
