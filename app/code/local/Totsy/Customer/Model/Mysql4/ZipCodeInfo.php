<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Customer_Model_Mysql4_ZipCodeInfo
    extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('totsycustomer/zipCodeInfo', 'customer_zip_code_info_id');
    }

}
