<?php
/**
 * Change the name of the controller for the client
 */
class Inchoo_Api_TotsyController extends Mage_Api_Controller_Action
{  
    
    /**
     * Access like http://magento.ce/index.php/api/totsy/json
     */
    public function jsonAction()
    {
        /* inchoo_api_json => HANDLER from api.xml */
    	//HP --Start
    	$response = Mage::app()->getResponse();
    	$response->setHeader('Content-type', 'application/json');
    	//HP --End
        $this->_getServer()->init($this, 'inchoo_api_json')
            ->run();
    }
    
    public function productAction()
    {
    	$httpMethod = $this->getRequest()->getMethod();
    	$params = $this->getRequest()->getParams();
    	switch($httpMethod){
    		case 'POST':
    			break;
    		case 'GET':
    			$auth = Mage::helper('inchoo_api')->_apiLogin('sgao', 'test123');
    			$result = Mage::helper('inchoo_api')->_setHttpRequest($auth);
    			$sessionId = $result['result'];
    			if(isset($params)){
	    			if(isset($params['category'])){
	    				$categoryList = json_decode($params['category']);
	    				
	    				$params = Mage::helper('inchoo_api')->_setParamsToJson($sessionId, 'catalog_product.collection', $categoryList);
	    			}elseif(isset($params['id'])){
	    				$productId = $params['id'];
	    				$params = Mage::helper('inchoo_api')->_setParamsToJson($sessionId, 'catalog_product.info', $productId);
	    			}
	    			$result = Mage::helper('inchoo_api')->_setHttpRequest($params);
					$response = Mage::helper('inchoo_api')->_setHttpResponse($result);
					echo $response;
    			}
    			break;
    		case 'PUT':
    			break;
    		default:
    			break;
    	}
    }
    
    public function userAction()
    {
    	$httpMethod = $this->getRequest()->getMethod();
    	$params = $this->getRequest()->getParams();
    	
    	switch($httpMethod){
    		case 'POST':
    			$auth = Mage::helper('inchoo_api')->_apiLogin('sgao', 'test123');
    			$result = Mage::helper('inchoo_api')->_setHttpRequest($auth);
    			$sessionId = $result['result'];
    			if(isset($pamams['id']) && !empty($params['id'])){
	    			if(isset($params['update'])){
	    				$params = Mage::helper('inchoo_api')->_setParamsToJson($sessionId, 'customer.update', $params['update']);
	    			
	    			}elseif(isset($params['create'])){
	    				$params = Mage::helper('inchoo_api')->_setParamsToJson($sessionId, 'customer.create', $params['create']);
	    				
    				}elseif(isset($params['address'])){
    					$params = Mage::helper('inchoo_api')->_setParamsToJson($sessionId, 'customer_address.create', $params['id']);
	    				
    				}
    				$result = Mage::helper('inchoo_api')->_setHttpRequest($params);
	    			$response = Mage::helper('inchoo_api')->_setHttpResponse($result);
					echo $response;
    			}
    			break;
    		case 'GET':
    			$auth = Mage::helper('inchoo_api')->_apiLogin('sgao', 'test123');
    			$result = Mage::helper('inchoo_api')->_setHttpRequest($auth);
    			$sessionId = $result['result'];
    			if(isset($params['id']) && ! isset($params['address'])){
	    			$params = Mage::helper('inchoo_api')->_setParamsToJson($sessionId, 'customer.info', $params['id']);
	    		
    			}elseif(isset($params['id']) && isset($params['address']) && ! $params['address']){
    				$params = Mage::helper('inchoo_api')->_setParamsToJson($sessionId, 'customer.address', $params['id']);
	    			
    			}elseif(isset($params['id']) && isset($params['address']) && !! $params['address']){
    				$params = Mage::helper('inchoo_api')->_setParamsToJson($sessionId, 'customer_address.info', $params['address']);
	    			
    			}
    			$result = Mage::helper('inchoo_api')->_setHttpRequest($params);
	    		$response = Mage::helper('inchoo_api')->_setHttpResponse($result);
	    		echo $response;
    			
    			break;
    		case 'PUT':
    			$auth = Mage::helper('inchoo_api')->_apiLogin('sgao', 'test123');
    			$result = Mage::helper('inchoo_api')->_setHttpRequest($auth);
    			$sessionId = $result['result'];
    			if(isset($pamams)){
	    			if(isset($params['update'])){
	    				$params = Mage::helper('inchoo_api')->_setParamsToJson($sessionId, 'customer.update', $params['update']);
	    				$result = Mage::helper('inchoo_api')->_setHttpRequest($params);
	    				$response = Mage::helper('inchoo_api')->_setHttpResponse($result);
						echo $response;
	    			}
    			}
    			break;
    	}
    }
	
    public function addressAction()
    {
    	$httpMethod = $this->getRequest()->getMethod();
    	$params = $this->getRequest()->getParams();
    	
    	try{
	    	switch($httpMethod){
	    		case 'DELETE':
	    			$auth = Mage::helper('inchoo_api')->_apiLogin('sgao', 'test123');
	    			$result = Mage::helper('inchoo_api')->_setHttpRequest($auth);
	    			$sessionId = $result['result'];
	    			if(isset($params['id'])){
	    				$params = Mage::helper('inchoo_api')->_setParamsToJson($sessionId, 'customer_address.delete', $params['id']);
		    			$result = Mage::helper('inchoo_api')->_setHttpRequest($params);
		    			$response = Mage::helper('inchoo_api')->_setHttpResponse($result);
						echo $response;
	    			}
	    			break;
	    		case 'GET':
	    			$auth = Mage::helper('inchoo_api')->_apiLogin('sgao', 'test123');
	    			$result = Mage::helper('inchoo_api')->_setHttpRequest($auth);
	    			$sessionId = $result['result'];
	    			if(isset($params['id'])){
	    				$params = Mage::helper('inchoo_api')->_setParamsToJson($sessionId, 'customer_address.info', $params['id']);
		    			$result = Mage::helper('inchoo_api')->_setHttpRequest($params);
		    			$response = Mage::helper('inchoo_api')->_setHttpResponse($result);
						echo $response;
	    			}
	    			break;
	    		case 'PUT':
	    			$auth = Mage::helper('inchoo_api')->_apiLogin('sgao', 'test123');
	    			$result = Mage::helper('inchoo_api')->_setHttpRequest($auth);
	    			$sessionId = $result['result'];
	    			if(isset($params['info'])){
	    				$params = Mage::helper('inchoo_api')->_setParamsToJson($sessionId, 'customer_address.update', $params['info']);
		    			$result = Mage::helper('inchoo_api')->_setHttpRequest($params);
		    			$response = Mage::helper('inchoo_api')->_setHttpResponse($result);
						echo $response;
	    			}
	    			break;
	    	}
    	}catch(Exception $e){
    		echo $e->getMessage();
    	}
    }
    
    public function eventAction()
    {   
    	$response = $this->getResponse();
    	$httpMethod = $this->getRequest()->getMethod();
    	$params = $this->getRequest()->getParams();
    	
    	try{
    		switch($httpMethod){
	    		case 'GET':
	    			$auth = Mage::helper('inchoo_api')->_apiLogin('sgao', 'test123');
	    			$result = Mage::helper('inchoo_api')->_setHttpRequest($auth);
	    			$sessionId = $result['result'];
	    			if(isset($params['id'])){
	    				$params = Mage::helper('inchoo_api')->_setParamsToJson($sessionId, 'catalog_category.info', $params['id']);
		    			
	    			}else{
	    				$params = Mage::helper('inchoo_api')->_setParamsToJson($sessionId, 'catalog_category.currentevent');
		    	
	    			}
	    			$result = Mage::helper('inchoo_api')->_setHttpRequest($params);
		    		$response = Mage::helper('inchoo_api')->_setHttpResponse($result);
					echo $response;
	    			break;
	    	}
    	}catch(Exception $e){
    		echo $e->getMessage();
    	}
    	
    }
    
	
}
