<?php
/**
 * @category    Totsy
 * @package     Totsy\Api\Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Api_Model_Client extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('totsyapi/client');
    }
}