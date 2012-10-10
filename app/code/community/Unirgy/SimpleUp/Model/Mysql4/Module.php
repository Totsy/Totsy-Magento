<?php

class Unirgy_SimpleUp_Model_Mysql4_Module extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('usimpleup/module', 'module_id');
    }
}