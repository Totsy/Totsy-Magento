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


class Totsy_Linkshare_Model_Mysql4_transactions extends Mage_Core_Model_Mysql4_Abstract{
   
    protected function _construct(){
        $this->_init('linkshare/transactions', 'id');
    }
    
    /*public function loadByCustomerId($customerId){
        $readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select()
                ->from($this->getMainTable())
                ->where('customer_id=:customer_id');
        $result = $readAdapter->fetchRow($select, array('customer_id' => $customerId));
        if (!$result) {
           $result = array(); 
        }
        return $result;        
    }

    public function loadByCustomerEmail($customerEmail){
        $readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select()
                ->from($this->getMainTable())
                ->where('customer_email=:customer_email');
        $result = $readAdapter->fetchRow($select, array('customer_email' => $customerEmail));
        if (!$result) {
           $result = array(); 
        }
        return $result;        
    }*/
}
