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

class Enterprise_PageCache_Model_Processor_Default
{
    /**
     * @var Enterprise_PageCache_Model_Container_Placeholder
     */
    private $_placeholder;

    /**
     * Disable cache for url with next GET params
     *
     * @var array
     */
    protected $_noCacheGetParams = array('___store', '___from_store');

    /**
     * Check if request can be cached
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function allowCache(Zend_Controller_Request_Http $request)
    {
        foreach ($this->_noCacheGetParams as $param) {
            if (!is_null($request->getParam($param, null))) {
                return false;
            }
        }
        if (Mage::getSingleton('core/session')->getNoCacheFlag()) {
            return false;
        }
        return true;
    }

    /**
     * Prepare response body before caching
     *
     * @param Zend_Controller_Response_Http $response
     * @return string
     */
    public function prepareContent(Zend_Controller_Response_Http $response)
    {
        $content = $response->getBody();
//        Mage::log($content);
        $placeholders = array();
        preg_match_all(
            Enterprise_PageCache_Model_Container_Placeholder::HTML_NAME_PATTERN,
            $content,
            $placeholders,
            PREG_PATTERN_ORDER
        );
        $placeholders = array_unique($placeholders[1]);
        try {
            foreach ($placeholders as $definition) {
                $this->_placeholder = Mage::getModel('enterprise_pagecache/container_placeholder', $definition);
                $content = preg_replace_callback($this->_placeholder->getPattern(),
                    array($this, '_getPlaceholderReplacer'), $content);
            }
            $this->_placeholder = null;
        } catch (Exception $e) {
            $this->_placeholder = null;
            throw $e;
        }
//        Mage::log($content);
        return $content;
    }

    /**
     * Retrieve placeholder replacer
     *
     * @param array $matches Matches by preg_replace_callback
     * @return string
     */
    protected function _getPlaceholderReplacer($matches)
    {
        $container = $this->_placeholder->getContainerClass();
        /**
         * In developer mode blocks will be rendered separately
         * This should simplify debugging _renderBlock()
         */
        if ($container && !Mage::getIsDeveloperMode()) {
            $container = new $container($this->_placeholder);
            $blockContent = $matches[1];
            $container->saveCache($blockContent);
        }
        return $this->_placeholder->getReplacer();
    }


    /**
     * Return cache page id with application. Depends on GET super global array.
     *
     * @param Enterprise_PageCache_Model_Processor $processor
     * @param Zend_Controller_Request_Http $request
     * @return string
     */
    public function getPageIdInApp(Enterprise_PageCache_Model_Processor $processor)
    {
        return $this->getPageIdWithoutApp($processor);
    }

    /**
     * Return cache page id without application. Depends on GET super global array.
     *
     * @param Enterprise_PageCache_Model_Processor $processor
     * @return string
     */
    public function getPageIdWithoutApp(Enterprise_PageCache_Model_Processor $processor)
    {
        return $processor->getRequestId();
        $queryParams = $_GET;
        ksort($queryParams);
        $queryParamsHash = md5(serialize($queryParams));
        return $processor->getRequestId() . '_' . $queryParamsHash;
    }

    /**
     * Get request uri based on HTTP request uri and visitor session state
     *
     * @deprecated after 1.8
     * @param Enterprise_PageCache_Model_Processor $processor
     * @param Zend_Controller_Request_Http $request
     * @return string
     */
    public function getRequestUri(Enterprise_PageCache_Model_Processor $processor,
        Zend_Controller_Request_Http $request
    ) {
    }
}
