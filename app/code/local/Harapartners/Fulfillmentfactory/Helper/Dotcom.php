<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */
class Harapartners_Fulfillmentfactory_Helper_Dotcom extends Mage_Core_Helper_Abstract{
    //const API_KEY = '53e04657f78564b584b0ff2682ae89c4';
    //const API_PASSWORD = '36e4c4ad2ff195b7becd59dbdb23550d';
    //const DOTCOM_BASE_URL = 'https://cwa.dotcomdistribution.com/dcd_api_test/DCDAPIService.svc';
    
    protected static $_apiKey = '';
    protected static $_apiPassword = '';
    protected static $_apiUrl = '';
    
    protected function _getConfig() {
        //Please see System->Configuration->Sales->Order Fulfillment Settings
        self::$_apiKey = Mage::getStoreConfig('fulfillmentfactory_options/dotcom_setting/fulfillment_dotcom_api_key');
        self::$_apiPassword = Mage::getStoreConfig('fulfillmentfactory_options/dotcom_setting/fulfillment_dotcom_api_password');
        self::$_apiUrl = Mage::getStoreConfig('fulfillmentfactory_options/dotcom_setting/fulfillment_dotcom_api_base_url');
        
        //for default testing environment
        if(empty(self::$_apiKey)) {
            self::$_apiKey = '53e04657f78564b584b0ff2682ae89c4';
        }
        
        if(empty(self::$_apiPassword)) {
            self::$_apiPassword = '36e4c4ad2ff195b7becd59dbdb23550d';
        }
        
        if(empty(self::$_apiUrl)) {
            self::$_apiKey = 'https://cwa.dotcomdistribution.com/dcd_api_test/DCDAPIService.svc';
        }
    }
    
    /**
     * generate authorization header (HMAC encryption)
     *
     * @param string $uri
     * @return string encrypted string
     */
    protected function _generateAuthHeader($uri) {
        $this->_getConfig();
        
        //get HAMC hash string
        $hash = hash_hmac('md5', $uri, self::$_apiPassword);
        
        //base64 encryption and concatenate with API KEY
        return self::$_apiKey . ':' . base64_encode(pack('H*', $hash));
    }
    
    /**
     * read xml string (namespace is 'a')
     *
     * @param string $str
     * @return SimpleXMLElement $xml
     */
    protected function _readXMLString($str) {
        $this->_getConfig();
        
        $xml = new SimpleXMLElement($str);
        $items = $xml->children()->children('a', TRUE);    // get items base on namespace 'a'
        
        return $items;
    }
    
    /**
     * Send query request
     *
     * @param string $uri
     * @param array $header
     * @return array response body
     */
    protected function _sendQueryRequest($uri, $header=array()) {
        try {
            $this->_getConfig();
            
            $client = new Zend_Http_Client($uri);
            
            $header['Accept-encoding'] = 'gzip,deflate';
            $header['Authorization'] = $this->_generateAuthHeader($uri);
            $client->setHeaders($header);
            
            $response = $client->request();

            if(isset($response)) {
                $body = $response->getBody();
                return $body;
            }
        }
        catch(Exception $e) {
            throw new Exception('Send query to DOTcom failed: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * post xml request
     *
     * @param string $uri
     * @param string $xml    XML string
     * @param array $header
     * @return array response body
     */
    protected function _postXMLRequest($uri, $xml, $header=array()) {
        try {
            $this->_getConfig();
            
            $client = new Zend_Http_Client($uri);
            
            $header['Content-type'] = 'text/xml; charset=utf-8';
            $header['Accept-encoding'] = 'gzip,deflate';
            $header['Authorization'] = $this->_generateAuthHeader($uri);    
            $client->setHeaders($header);
            
            $client->setRawData($xml);
            
            $response = $client->request('POST');
            echo print_r($response, 1);//TEST
            
            if(isset($response)) {
                $body = $response->getBody();
                return $body;
            }
        }
        catch(Exception $e) {
            throw new Exception('Post xml data to DOTcom failed: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * get shipping methods list from Dotcom API
     *
     */
    public function getShippingMethodListFromDotcom() {
        $this->_getConfig();
        
        $uri = self::$_apiUrl . '/shipmethod';
        
        $body = $this->_sendQueryRequest($uri);
        if(!empty($body)) {
            $items = $this->_readXMLString($body);
            return $items;
        }
        
        return false;
    }
    
    /**TODO put correct mapping list
     * mapping Magento shipping method to Dotcom shipping method code
     *
     * @param string $method Magento shipping method
     * @return string Dotcom shipping method code
     */
    public function getDotcomShippingMethod($method) {
        $this->_getConfig();
        return 'SP';
    }

    /**converting us territories to countrycode
     *
     * @param string $method Magento shipping method
     * @return string Dotcom shipping method code
     */
    public function getCountryCodeUsTerritories($state) {
		//country code hack for US territories, will need to be revisited for international shipping changes
		$country = "US";
		$territoryStates = array('AS', 'FM', 'GU', 'MH', 'MP', 'PW', 'PR', 'VI');
		
		if(in_array($state, $territoryStates)){
			$country = $state;
		}
		
		return $country;
    }
    
    /**
     * submit products (items API of Dotcom) to Dotcom
     *
     * @param array $products
     */
    public function submitProductItems($dataXML) {
        $this->_getConfig();
        $uri = self::$_apiUrl . '/item';
        $response = $this->_postXMLRequest($uri, $dataXML);
        
        return $this->_readXMLString($response);
    }
    
    /**
     * submit orders to Dotcom
     *
     * @param array $orders
     */
    public function submitOrders($dataXML) {
        $this->_getConfig();
        $uri = self::$_apiUrl . '/order';
        $response = $this->_postXMLRequest($uri, $dataXML);
        
        return $this->_readXMLString($response);
    }
    
    /**
     * submit Purchase Orders to Dotcom
     *
     * @param array $orders
     */
    public function submitPurchaseOrders($dataXML) {
        $this->_getConfig();
        $uri = self::$_apiUrl . '/purchase_order';
        
        $response = $this->_postXMLRequest($uri, $dataXML);
        
        return $this->_readXMLString($response);
    }
    
    /**
     * get Current Inventory
     *
     * @return SimpleXMLElement inventory
     */
    public function getInventory() {
        $this->_getConfig();
        $uri = self::$_apiUrl . '/inventory';
        
        $body = $this->_sendQueryRequest($uri);
        if(!empty($body)) {
            $items = $this->_readXMLString($body);
            return $items;
        }
        
        return false;
    }
    
    /**
     * get stock info
     *
     * @param string $fromDate
     * @param string $toDate
     * @return SimpleXMLElement $xml
     */
    public function getStock($fromDate='', $toDate='') {
        $this->_getConfig();
        $uri = self::$_apiUrl . '/stockstatus';
        $uri  = $uri . '?fromDate=' . urlencode($fromDate) . '&toDate=' . urlencode($toDate);    //GET Method

        $body =  $this->_sendQueryRequest($uri);
        
        if(!empty($body)) {
            $stock = $this->_readXMLString($body);
            
            return $stock;
        }
        
        return false;
    }
    
    /**
     * get order status
     *
     * @param string $fromDate
     * @param string $toDate
     * @return SimpleXMLElement $xml
     */
    public function getOrderStatus($fromDate='', $toDate='') {
        $this->_getConfig();
        $uri = self::$_apiUrl . '/order';
        $uri  = $uri . '?fromOrdDate=' . urlencode($fromDate) . '&toOrdDate=' . urlencode($toDate);    //GET Method
        
        $body =  $this->_sendQueryRequest($uri);
        
        if(!empty($body)) {
            $statusData = $this->_readXMLString($body);
            
            return $statusData;
        }
        
        return false;
    }
    
    /**
     * get shipment info
     *
     * @param string $fromDate
     * @param string $toDate
     * @return SimpleXMLElement $xml
     */
    public function getShipment($fromDate='', $toDate='') {
        $this->_getConfig();
        $uri = self::$_apiUrl . '/shipment';
        $uri  = $uri . '?fromShipDate=' . urlencode($fromDate) . '&toShipDate=' . urlencode($toDate);    //GET Method
        
        $body =  $this->_sendQueryRequest($uri);
        
        if(!empty($body)) {
            $shipments = $this->_readXMLString($body);
            
            return $shipments;
        }
        
        return false;
    }
}
