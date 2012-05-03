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

class Harapartners_Unsubcentral_Model_Mysql4_Item extends Mage_Core_Model_Resource_Db_Abstract{
    
    protected $_read;
    
    protected function _construct(){
        $this->_init('unsubcentral/item', 'unsubcentral_request_id');
        $this->_read = $this->_getReadAdapter();
    }
    
    
    public function loadByEmail($email){
        $result = array();
        $select = $this->_read->select()
                ->from($this->getMainTable())
                ->where('subscriber_email=:subscriber_email');

        $result = $this->_read->fetchRow($select, array('subscriber_email'=>$email));

        if (!$result) {
           $result = array(); 
        }

        return $result;
    }
}
