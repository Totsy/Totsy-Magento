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

class Harapartners_Categoryevent_Model_Cache_Index extends Enterprise_PageCache_Model_Container_Abstract {
    
	const CACHE_TAG_PREFIX = 'catagoryevent_index';

    protected function _getIdentifier() {
        $params = Mage::registry('application_params');
        $scopeCode = '';
        if(isset($params['scope_code'])) {
            $scopeCode = $params['scope_code'];
        }
        return $scopeCode;
    }

    protected function _getCacheId() {
        return md5(self::CACHE_TAG_PREFIX . $this->_getIdentifier());
    }

    protected function _renderBlock() {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');

        $block = new $block;
        $block->setNameInLayout('categoryevent');
        $block->setTemplate($template);
        $block->setLayout(Mage::app()->getLayout());

        return $block->toHtml();
    }

}