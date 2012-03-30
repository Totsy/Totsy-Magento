<?php

class Harapartners_Categoryevent_Model_Cache_Index extends Enterprise_PageCache_Model_Container_Abstract {
    
	const CACHE_TAG_PREFIX = 'catagoryevent_index';

    protected function _getCacheId() {
        return md5(self::CACHE_TAG_PREFIX);
    }

    
//    protected function _getAdditionalCacheId(){
//    	return md5('CONTAINER_EVENT_INDEX_INDEX_' . $this->_placeholder->getAttribute('cache_id'));
//    }
    
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