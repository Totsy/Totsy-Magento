<?php
/**
 * @category    Totsy
 * @package     Totsy\Api\Model\Mysql4
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Api_Model_Mysql4_Client extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('totsyapi/client', 'totsyapi_client_id');
    }
}
