<?php
/**
 * @category    Totsy
 * @package     Totsy_CatalogFeed_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

abstract class Totsy_CatalogFeed_Model_Feed_Json
    extends Totsy_CatalogFeed_Model_Feed_Abstract
{
    protected $_content = array();

    /**
     * Build the string contents of this feed. This is called after the feed
     * has been built inside the generate() method.
     *
     * @return string
     */
    protected function _getFeedContent()
    {
        return json_encode($this->_content);
    }
}
