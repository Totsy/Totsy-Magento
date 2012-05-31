<?php
/**
 * @category    Totsy
 * @package     Totsy_Core_Helper
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Core_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Determine the runtime application environment.
     * This token is stored in configuration at node 'global/environment'.
     *
     * @return string
     */
    public function getEnvironment()
    {
        $env = (string) Mage::getConfig()->getNode('global/environment') ?: 'dev';
        return $env;
    }
}
