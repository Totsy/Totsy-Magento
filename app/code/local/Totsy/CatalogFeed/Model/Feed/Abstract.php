<?php
/**
 * @category    Totsy
 * @package     Totsy_CatalogFeed_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

abstract class Totsy_CatalogFeed_Model_Feed_Abstract
{
    /**
     * Generate a feed, and return it's string contents.
     *
     * @param array $options Feed modifier options.
     *
     * @return string
     */
    public abstract function generate(array $options = array());
}
