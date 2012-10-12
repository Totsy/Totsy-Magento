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

class Harapartners_Promotionfactory_Model_Mysql4_Virtualproductcoupon extends Mage_Core_Model_Mysql4_Abstract{
    
    protected function _construct(){
        $this->_init('promotionfactory/virtualproductcoupon', 'entity_id');
    }

    public function loadTotalCountByProductId($productId) {
		//Try to count first, if 0, return false
		$readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select()
                ->from($this->getMainTable(), array('coupon_count' => new Zend_Db_Expr('COUNT(entity_id)')))
                ->where( "product_id=:product_id");
        $result = $readAdapter->fetchRow($select, array('product_id' => $productId));
        
        if(isset($result['coupon_count'])){
        	return $result['coupon_count'];
        }
        
        return 0;
    }

}