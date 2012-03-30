<?php


class Harapartners_Paymentfactory_Model_Api_ExtendedSoapClient extends SoapClient
{
    /**
     * Store Id for retrieving config data
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Store Id setter
     *
     * @param int $storeId
     * @return Mage_Cybersource_Model_Api_ExtendedSoapClient
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Store Id getter
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeId;
    }

    /**
     * XPaths that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataXPaths = array(
        '//*[contains(name(),\'merchantID\')]/text()',
        '//*[contains(name(),\'card\')]/*/text()',
        '//*[contains(name(),\'UsernameToken\')]/*/text()'
    );

    public function __construct($wsdl, $options = array())
    {
        parent::__construct($wsdl, $options);
    }

    protected function getBaseApi()
    {
        return Mage::getSingleton('cybersource/soap');
    }

    public function __doRequest($request, $location, $action, $version, $oneWay = 0)
    {
        $api = $this->getBaseApi();
        $user = $api->getConfigData('merchant_id', $this->getStoreId());
        $password = $api->getConfigData('security_key', $this->getStoreId());
        $soapHeader = "<SOAP-ENV:Header xmlns:SOAP-ENV=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:wsse=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd\"><wsse:Security SOAP-ENV:mustUnderstand=\"1\"><wsse:UsernameToken><wsse:Username>$user</wsse:Username><wsse:Password Type=\"http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText\">$password</wsse:Password></wsse:UsernameToken></wsse:Security></SOAP-ENV:Header>";

        $requestDOM = new DOMDocument('1.0');
        $soapHeaderDOM = new DOMDocument('1.0');
        $requestDOM->loadXML($request);
        $soapHeaderDOM->loadXML($soapHeader);

        $node = $requestDOM->importNode($soapHeaderDOM->firstChild, true);
        $requestDOM->firstChild->insertBefore(
        $node, $requestDOM->firstChild->firstChild);

        $request = $requestDOM->saveXML();
        $requestDOMXPath = new DOMXPath($requestDOM);
        foreach ($this->_debugReplacePrivateDataXPaths as $xPath) {
            foreach ($requestDOMXPath->query($xPath) as $element) {
                $element->data = '***';
            }
        }

        $debugData = array('request' => $requestDOM->saveXML());
        try {
            $response = parent::__doRequest($request, $location, $action, $version, $oneWay);
        }
        catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $api->debugData($debugData);
            throw $e;
        }

        $debugData['result'] = $response;
        $api->debugData($debugData);

        return $response;
    }
}
