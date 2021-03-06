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

class Harapartners_Stockhistory_Model_Mysql4_Transaction extends Mage_Core_Model_Mysql4_Abstract {
    
    public function _construct() {
        $this->_init('stockhistory/transaction', 'id');
    }
    
    public function loadByProductId($productId){
        $readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select()
                ->from($this->getMainTable())
                ->where('product_id=:product_id');
        $result = $readAdapter->fetchRow($select, array('product_id' => $productId));
        if (!$result) {
           $result = array(); 
        }
        return $result;
    }
    
}