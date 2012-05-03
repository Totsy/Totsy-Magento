<?php
class Harapartners_Recurringmembership_Model_Mysql4_Profile extends Mage_Core_Model_Mysql4_Abstract{
    
    protected function _construct(){
        $this->_init('recurringmembership/profile', 'entity_id');
    }
    
}