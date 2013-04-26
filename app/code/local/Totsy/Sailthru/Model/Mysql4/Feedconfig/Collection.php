<?php

class Totsy_Sailthru_Model_Mysql4_Feedconfig_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract{
   
    public function _construct(){
        $this->_init('sailthru/feedconfig');
    }
}