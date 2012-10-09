<?php

class Unirgy_RapidFlow_Model_Mysql4_Catalog_Abstract extends Unirgy_RapidFlow_Model_Mysql4_Abstract
{
    public function __construct()
    {
        parent::__construct();

        $this->_eav = Mage::getSingleton('eav/config');
        $this->_read = $this->_res->getConnection('catalog_read');
        $this->_write = $this->_res->getConnection('catalog_write');
    }
}