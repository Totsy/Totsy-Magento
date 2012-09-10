<?php
/**
 * @category    Totsy
 * @package     Totsy_Akamai_Helper
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Akamai_Helper_Service_Ccu
{
    const CCU_API_WDSL_URL = 'https://ccuapi.akamai.com/ccuapi-axis.wsdl';

    const CCU_CONFIG_PATH_USERNAME = 'web/akamai/username';
    const CCU_CONFIG_PATH_PASSWORD = 'web/akamai/password';

    /**
     * The SOAP client.
     *
     * @var \SoapClient
     */
    protected $_client;

    /**
     * The Akamai CCU username.
     *
     * @var string
     */
    protected $_username;

    /**
     * The Akamai CCU password.
     *
     * @var string
     */
    protected $_password;

    public function __construct($username = null, $password = null)
    {
        $this->_client = new SoapClient(self::CCU_API_WDSL_URL);
        $this->_username = $username ?: Mage::getStoreConfig(self::CCU_CONFIG_PATH_USERNAME);
        $this->_password = $password ?: Mage::getStoreConfig(self::CCU_CONFIG_PATH_PASSWORD);
    }

    /**
     * Issue a purge request to the Akamai Content Control Utility.
     *
     * @param array|string $url
     *
     * @return int
     */
    public function purge($url)
    {
        $url = (array) $url;
        $this->_client->purgeRequest(
            $this->_username, // name
            $this->_password, // pwd
            '',               // network (default: production)
            array(),          // opt
            $url              // uri
        );

        return 0;
    }

    /**
     * @param \SoapClient $client
     */
    public function setClient($client)
    {
        $this->_client = $client;
    }

    /**
     * @return \SoapClient
     */
    public function getClient()
    {
        return $this->_client;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->_password = $password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->_username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->_username;
    }
}
