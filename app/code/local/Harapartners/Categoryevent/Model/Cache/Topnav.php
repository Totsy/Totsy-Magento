<?php

class Harapartners_Categoryevent_Model_Cache_Topnav extends Enterprise_PageCache_Model_Container_Abstract
{
    const CACHE_TAG_PREFIX = 'catagoryevent_topnav';

    /**
     * Get identifier from cookies
     *
     * @return string
     */
    protected function _getIdentifier()
    {
        return '';
    }
	
//    protected function _getAdditionalCacheId(){
//    	return md5('CONTAINER_EVENT_INDEX_TOPNAV_' . $this->_placeholder->getAttribute('cache_id'));
//    }
    /**
     * Get cache identifier
     *
     * @return string
     */
    protected function _getCacheId()
    {
        return md5(self::CACHE_TAG_PREFIX . $this->_getIdentifier());
    }

    /**
     * Render block content
     *
     * @return string
     */
    protected function _renderBlock()
    {
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
