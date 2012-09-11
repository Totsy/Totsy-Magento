<?php
/**
 * @category    Totsy
 * @package     Totsy_Cdn_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

interface Totsy_Cdn_Model_CdnInterface
{
    /**
     * Issue a purge request to the Akamai Content Control Utility.
     *
     * @param array|string $url The URL(s) to purge cache for.
     * @return bool TRUE when successful, or FALSE otherwise.
     */
    public function purge($url);
}
