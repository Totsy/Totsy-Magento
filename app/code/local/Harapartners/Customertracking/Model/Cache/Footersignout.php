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

class Harapartners_Customertracking_Model_Cache_Footersignout extends Enterprise_PageCache_Model_Container_Abstract {
    
    const CACHE_TAG_PREFIX = 'CUSTOMERTRACKING_FOOTERSIGNOUT';
    
    protected function _getIdentifier(){
        return $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER, '');
    }

    protected function _getCacheId(){
        return md5(self::CACHE_TAG_PREFIX . $this->_getIdentifier() );
    }
    
    protected function _renderBlock() {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');

        $block = new $block;
        $block->setNameInLayout('customertracking.footersignout');
        $block->setTemplate($template);
        $block->setLayout(Mage::app()->getLayout());
        
        //clean up is done within toHtml() so that regular page load follows the same logic

        return $block->toHtml();
    }

}