<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_PageCache
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Catalog product view processor
 */
class Enterprise_PageCache_Model_Processor_Product extends Enterprise_PageCache_Model_Processor_Default
{
    /**
     * Key for saving product id in metadata
     */
    const METADATA_PRODUCT_ID = 'current_product_id';
    
    //Harapartners, Jun, Catalog pages are updated at very high frequency (due to reservation fluctuation), refresh adaptively
    //Prime number preferred
    const PAGE_REFRESH_FACTOR = 400;

    /**
     * Prepare response body before caching
     *
     * @param Zend_Controller_Response_Http $response
     * @return string
     */
    public function prepareContent(Zend_Controller_Response_Http $response)
    {
        $cacheInstance = Enterprise_PageCache_Model_Cache::getCacheInstance();

        /** @var Enterprise_PageCache_Model_Processor */
        $processor = Mage::getSingleton('enterprise_pagecache/processor');
        $countLimit = Mage::getStoreConfig(Mage_Reports_Block_Product_Viewed::XML_PATH_RECENTLY_VIEWED_COUNT);
        // save recently viewed product count limit
        $cacheId = $processor->getRecentlyViewedCountCacheId();
        if (!$cacheInstance->getFrontend()->test($cacheId)) {
            $cacheInstance->save($countLimit, $cacheId);
        }
        // save current product id
        $product = Mage::registry('current_product');
        if ($product) {
            $cacheId = $processor->getRequestCacheId() . '_current_product_id';
            $cacheInstance->save($product->getId(), $cacheId);
            $processor->setMetadata(self::METADATA_PRODUCT_ID, $product->getId());
            Enterprise_PageCache_Model_Cookie::registerViewedProducts($product->getId(), $countLimit);
        }
        return parent::prepareContent($response);
    }
    
    //Harapartners, Jun, Catalog pages are updated at very high frequency (due to reservation fluctuation), refresh adaptively
    public function getPageIdWithoutApp(Enterprise_PageCache_Model_Processor $processor){
        list($usec, $sec) = explode(' ', microtime());
        $seed = (int) ($usec * 1000000);
        srand((int) ($seed));
        if(rand(1, self::PAGE_REFRESH_FACTOR) == 1){
            return md5($seed.rand());
        }
        return parent::getPageIdWithoutApp($processor);
    }
    
}
