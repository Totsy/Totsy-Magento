<?php

class Totsy_Promotions_Model_Mysql4_Banner_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract{
   
    public function _construct(){
        $this->_init('promotions/banner');
    }
}