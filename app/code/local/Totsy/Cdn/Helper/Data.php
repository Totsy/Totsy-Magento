<?php
/**
 * @category    Totsy
 * @package     Totsy_Cdn_Helper
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Cdn_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Issue a network purge request to the current CDN.
     *
     * @param array|string $url     The URL(s) to be purged.
     * @param string       $network The CDN to issue the request to.
     *
     * @return bool TRUE when successful, or FALSE on failure.
     */
    public function purge($url, $network = null)
    {
        if (null == $network) {
            $network = (string) Mage::getStoreConfig('cdn/default');
        }

        $networkClass = Mage::getStoreConfig("cdn/networks/$network");
        if (!$networkClass) {
            Mage::throwException("No class configured for CDN '$network'");
        }

        if (!class_implements($networkClass, 'Totsy_Cdn_Model_CdnInterface')) {
            Mage::throwException(
                "Class '$networkClass' configured for network '$network' does " .
                    " not implement the appropriate interface."
            );
        }

        $cdn = new $networkClass;
        return $cdn->purge($url);
    }
}
