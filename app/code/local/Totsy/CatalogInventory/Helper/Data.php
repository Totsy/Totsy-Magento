<?php

class Totsy_CatalogInventory_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_tags = array();
    protected $_prefix = 'TOTSY_CATALOGINVENTORY_RESERVE_PRODUCT_';
    protected $_lifetime = 1800;

    public function getReserveCount($productId) {
        $reserved = $this->_getReserveCountFromDb($productId);

        if($reserved !== false) {
            $this->setReserveCount($productId, $reserved);
        }

        return $reserved;
    }

    protected function _getReserveCountFromDb($productId) {
        $resource = Mage::getSingleton('core/resource');

        $quoteTable = $resource->getTableName('sales/quote_item');
        $orderTable = $resource->getTableName('sales/order');

        $connection = $resource->getConnection('core_read');

        $select = $connection->select()
            ->from(array('sfqi' => $quoteTable))
            ->joinLeft(array('parent'=>$quoteTable),'sfqi.parent_item_id=parent.item_id')
            ->joinLeft(array('sfo'=>$orderTable),'sfqi.quote_id=sfo.quote_id')
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('reserved' => 'sum(if(parent.item_id is not null,parent.qty,sfqi.qty))'))
            ->where('sfqi.product_id=?', $productId)
            ->where('sfo.entity_id is null');

        if(($quote = Mage::getSingleton('checkout/cart')->getQuote())
            && ($quote_id = $quote->getId())) {
            $select->where('sfqi.quote_id != ? ', $quote_id);
        }

        $reserved = $connection->fetchOne($select);
        if(is_null($reserved)) {
            $reserved = 0;
        }
        return $reserved;

    }

    public function setReserveCount($productId,$qty=null) {
        if(is_null($qty)) {
            $qty = $this->_getReserveCountFromDb($productId);

            if($qty === false) {
                return false;
            }
        }
        return Mage::app()->getCache()->save((string)$qty, $this->_getCacheKey($productId), $this->_tags, $this->_lifetime);
    }

    protected function _getCacheKey($productId) {
        return $this->_prefix.$productId;
    }

    public function changeReserveCount($productId,$delta) {
        if($delta == 0) {
            return true;
        }

        $cache = Mage::app()->getCache();

        if(method_exists($cache->getBackend(),'getRedis') && $cache->load($this->_getCacheKey($productId))) {
            $redis = $cache->getBackend()->getRedis();
            if($delta > 0) {
                return $redis->incrBy($cache->getBackend()->getCacheId($this->_getCacheKey($productId)),$delta);
            }
            return $redis->decrBy($cache->getBackend()->getCacheId($this->_getCacheKey($productId)),abs($delta));
        }

        return $this->setReserveCount($productId);
    }
}