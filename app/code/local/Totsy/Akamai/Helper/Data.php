<?php
/**
 * @category    Totsy
 * @package     Totsy_Akamai_Helper
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Akamai_Helper_Data
{
    public function purge($url)
    {
        $service = Mage::helper('akamai/service_ccu');
        return $service->purge($url);
    }
}
