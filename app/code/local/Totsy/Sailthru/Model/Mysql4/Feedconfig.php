<?php

class Totsy_Sailthru_Model_Mysql4_Feedconfig extends Mage_Core_Model_Mysql4_Abstract
{
   
    protected function _construct(){
        $this->_init('sailthru/feedconfig', 'entity_id');
    }

    public function getFeedConfigParams($hash){

    	$select = $this->_getReadAdapter()
            ->select()
            ->from($this->getMainTable())
            ->where('hash = ?', $hash);

        return $this->_getReadAdapter()->fetchAll($select);
    }
}

