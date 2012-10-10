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

        if(!Mage::registry('admin_preview_mode')) {
            // Each product must have a category/event
            $category = $product->getLiveCategory();
            if ($category && $categoryEventId = $category->getId()) {
                if ($product->canBeShowInCategory($categoryEventId)) {
                    $categoryId = $categoryEventId;
                } else {
                    return false;
                }
            } else {
                return false;
            }

            $category = Mage::getModel('catalog/category')->load($categoryId);
            Mage::register('current_category', $category);

            // Register current data and dispatch final events
            Mage::register('current_product', $product);
            Mage::register('product', $product);
        } else {
            $category = Mage::registry('current_category');

        }
        $product->setCategory($category);

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
}
