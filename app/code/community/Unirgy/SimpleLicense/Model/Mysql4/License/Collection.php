<?php

class Unirgy_SimpleLicense_Model_Mysql4_License_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('usimplelic/license');
    }
}