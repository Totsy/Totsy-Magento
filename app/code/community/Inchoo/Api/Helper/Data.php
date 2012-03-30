<?php
/**
 * @author      Branko Ajzele, ajzele@gmail.com
 * @category    Inchoo
 * @package     Inchoo_Api
 * @copyright   Copyright (c) Inchoo LLC (http://inchoo.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * This part is modified by Harapartners, original file is empty
 */
class Inchoo_Api_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function _setParamsToJson($sessionId, $call, $data = array())
    {
    	$params = array(
    				'jsonrpc' => '2.0',
    				'method' => 'call',
    				'params' => array($sessionId, $call, $data),
    				'id' => time()
				);
		return $params; 	
    }
    
    public function _apiLogin($user, $apiKey)
    {
    	$params = array(
    				'jsonrpc' => '2.0',
    				'method' => 'login',
    				'params' => array($user, $apiKey),
    				'id' => time()
		);
		return $params;	
    }
    
    public function _endSession($sessionId)
    {
    	$params = array(
    				'jsonrpc' => '2.0',
    				'method' => 'endSession',
    				'params' => array($sessionId),
    				'id' => time()
    	);
    	return $params;
    }
    
    public function _setHttpRequest($params)
    {
    	$serverApiUri = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'api/totsy/json';
    	$http = new Zend_Http_Client();
		$http->setUri($serverApiUri);
		$http->setMethod(Zend_Http_Client::POST);
		$http->setRawData(json_encode($params));
		$result = json_decode($http->request()->getBody(), true);
		return $result;
    }
    
    public function _setHttpResponse($result)
    {   
    	
    	if(! $result){
    		return;
    	}
    	elseif(is_null($result['error'])){
			return json_encode($result['result']);
		}else{
			return json_encode($result['error']['message']);
		}
    }
}
