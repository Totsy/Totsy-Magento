<?php
ini_set('memory_limit', '2G');	
$mageFilename = '../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);//->loadArea('frontend');


$time = time();

$params = array(
    'jsonrpc' => '2.0',
    'method' => 'login',
    'params' => array('sgao', 'test123'),
    'id' => $time
);        

$serverApiUri = "http://127.0.0.1/api/totsy/json";
//$serverApiUri = "http://stage.harapartners.com/totsy/api/totsy/json";
$http = new Zend_Http_Client();



$http->setUri($serverApiUri);
$http->setMethod(Zend_Http_Client::POST);
$http->setRawData(json_encode($params));

$result = json_decode($http->request()->getBody());

$sessionId = $result->result;

Zend_Debug::dump($sessionId, '$sessionId');

$data = array();
$data[] = 14;
//$data[] = array(
//	'firstname'		=>	'Song',
//	'lastname'		=>	'Gao',
//	'street'		=>	array('136 SW36th Street', '15th FL'),
//	'region'		=>	'New York',
//	'region_id'		=>	43,
//	'city'			=>	'New York City',
//	'country_id'	=>	'US',
//	'postcode'		=>	10018,
//);
$data1 = array('t.liu@harapartners.com', 'test123');
$params1 = array(
	'jsonrpc'	=> '2.0',
	'method'	=> 'call',
	'params'	=> array($sessionId, 'customer.auth', $data1),
	'id'		=> $time,
);
$http2 = new Zend_Http_Client();
$http2->setUri($serverApiUri);
$http2->setMethod(Zend_Http_Client::POST);
$http2->setRawData(json_encode($params1));
$result2 = json_decode($http2->request()->getBody());                

Zend_Debug::dump($result2, 'customer login');

$data3 = array();
$items = array();
$items[] = array(
	'product_id'	=> 19,
	'quantity'		=> 3,
);
//$data3[] = 14;
//$data3[] = $items;
//$data3[] = 1;
//$params2 = array(
//    'jsonrpc' => '2.0',
//    'method' => 'call',
//    'params' => array($sessionId, 'cart.create' , $data3),
//    'id' => $time
//);        
//
//$http2 = new Zend_Http_Client();
//$http2->setUri($serverApiUri);
//$http2->setMethod(Zend_Http_Client::POST);
//$http2->setRawData(json_encode($params2));
//$result2 = $http2->request()->getBody();                
//
//Zend_Debug::dump($result2, 'Create Quote');


$data = array();
$data[] = array(26,27);
$params2 = array(
    'jsonrpc' => '2.0',
    'method' => 'call',
    'params' => array($sessionId, 'catalog_product.info', 23213213213),
    'id' => $time
);        

$http2 = new Zend_Http_Client();
$http2->setUri($serverApiUri);
$http2->setMethod(Zend_Http_Client::POST);
$http2->setRawData(json_encode($params2));
$result2 = $http2->request()->getBody();                

Zend_Debug::dump($result2, '$result2');

$params3 = array(
    'jsonrpc' => '2.0',
    'method' => 'endSession',
    'params' => array($sessionId),
    'id' => $time
);   

$http3 = new Zend_Http_Client();
$http3->setUri($serverApiUri);
$http3->setMethod(Zend_Http_Client::POST);
$http3->setRawData(json_encode($params3));
$result3 = json_decode($http3->request()->getBody()); 

Zend_Debug::dump($result3, 'Logout');