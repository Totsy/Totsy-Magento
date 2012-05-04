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

class Harapartners_Stockhistory_Model_Mysql4_Vendor extends Mage_Core_Model_Mysql4_Abstract {
    
    protected $_read;
    
    public function _construct() {
        $this->_init('stockhistory/vendor', 'id');
        $this->_read = $this->_getReadAdapter();
        //$this->_write = $this->_getWriteAdapter();
    }
    
    public function loadByCode($code, $storeId = null) {
        $select = $this->_read->select()
            ->from($this->getMainTable())
            ->where('vendor_code' . ' = ?', $code);
        
        if(!!$storeId){
            $select->where('store_id=?', $storeId);
        }
        
        $rowData = $this->_read->fetchRow($select);
        
        if(!$rowData){
            $rowData = array();    
        }
        
        return $rowData;
    }
    
//    public function validateByCode($code, $storeId = null) {
//        $row = $this->loadByCode($code);
//        $vendorId = isset($row['id']) ? $row['id'] : null;
//        return $vendorId;
//    }
    
}