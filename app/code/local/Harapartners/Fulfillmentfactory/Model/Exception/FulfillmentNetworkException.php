<?php
/**
 * @category    Totsy
 * @package     Harapartners_Fulfillmentfactory
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

class Harapartners_Fulfillmentfactory_Model_Exception_FulfillmentNetworkException
    extends Exception
{
    /**
     * The request URI.
     *
     * @var string
     */
    protected $_requestUri;

    /**
     * The request body (XML string).
     *
     * @var string
     */
    protected $_requestXml;

    /**
     * The erroneous response received from the remote server.
     *
     * @var Zend_Http_Response
     */
    protected $_response;

    public function __construct($message, $response)
    {
        parent::__construct($message);
        $this->_response = $response;
    }

    /**
     * Get the request URI.
     *
     * @return string
     */
    public function getRequestUri()
    {
        return $this->_requestUri;
    }

    /**
     * Get he request body (XML string).
     *
     * @return string
     */
    public function getRequestXml()
    {
        return $this->_requestXml;
    }

    /**
     * Get the erroneous response received from the remote server.
     *
     * @return int|Zend_Http_Response
     */
    public function getResponse()
    {
        return $this->_response;
    }
}
