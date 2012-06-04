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
 */

class Harapartners_Categoryevent_Model_Cache_Topnav extends Enterprise_PageCache_Model_Container_Abstract
{
    const CACHE_TAG_PREFIX = 'catagoryevent_topnav';

    protected function _getIdentifier() {
        $cacheId = $_SERVER['REQUEST_URI']; //Different request param must be cached differently!
        
        $params = Mage::registry('application_params');
        $scopeCode = '';
        if(isset($params['scope_code'])) {
            $scopeCode = $params['scope_code'];
        }
        $cacheId .= '_' . $scopeCode;
        
        return $cacheId;
    }
    
    protected function _getCacheId() {
        return md5(self::CACHE_TAG_PREFIX . $this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
    }

    protected function _renderBlock() {
        $block = $this->_placeholder->getAttribute('block');
        //you can use a hard coded template here like xxxx_cached.phtml
        $template = $this->_placeholder->getAttribute('template');
        $block = new $block;
        $block->setNameInLayout('categoryevent');
        $block->setTemplate($template);
        $block->setLayout(Mage::app()->getLayout());
        return $block->toHtml();
    }

}
