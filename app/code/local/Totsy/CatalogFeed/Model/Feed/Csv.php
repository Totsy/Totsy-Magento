<?php
/**
 * @category    Totsy
 * @package     Totsy_CatalogFeed_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

abstract class Totsy_CatalogFeed_Model_Feed_Csv
    extends Totsy_CatalogFeed_Model_Feed_Abstract
{
    /**
     * The CSV column headers.
     *
     * @var array
     */
    protected $_header = array();

    /**
     * The file handle resource to read & write csv contents from & to.
     *
     * @var resource
     */
    protected $_handle;

    public function __construct(array $options = array())
    {
        $this->_handle = fopen('php://memory', 'rw');
        if (count($this->_header)) {
            fputcsv($this->_handle, $this->_header);
        }

        parent::__construct($options);
    }

    /**
     * Build the string contents of this feed. This is called after the feed
     * has been built inside the generate() method.
     *
     * @return string
     */
    protected function _getFeedContent()
    {
        rewind($this->_handle);
        $contents = stream_get_contents($this->_handle);

        fclose($this->_handle);
        return $contents;
    }
}
