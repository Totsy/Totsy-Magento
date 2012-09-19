<?php
/**
 * @category    Totsy
 * @package     Totsy_Cdn_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

interface Totsy_Cdn_Model_CdnInterface
{
    const PURGE_TYPE_REMOVE = 1;
    const PURGE_TYPE_INVALIDATE = 2;

    /**
     * Issue a purge request to the Akamai Content Control Utility.
     *
     * @param array|string $url  The URL(s) to purge cache for.
     * @param int          $type The type of purge request to send.
     *
     * @return bool TRUE when successful, or FALSE otherwise.
     */
    public function purge($url, $type = self::PURGE_TYPE_REMOVE);
}
