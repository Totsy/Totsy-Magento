<?php

class Unirgy_SimpleUp_Model_Mysql4_Module_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('usimpleup/module');
    }
}