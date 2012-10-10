<?php

class Unirgy_SimpleLicense_Model_Mysql4_License extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('usimplelic/license', 'license_id');
    }
}