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

class Harapartners_MobileApi_ProductController extends Mage_Core_Controller_Front_Action{
	
	public function indexAction(){
		$params = $this->getRequest()->getParams();
		$response = Mage::app()->getResponse();
		$response->setHeader('Content-type', 'application/json', true);
		try{
			if(count($params)== 1 && isset($params['id']) && !!$params['id']){
				// a single product
				$productId = $params['id'];
				$product = Mage::getModel('catalog/product')->load($productId);
				$result = Mage::helper('mobileapi')->getProductInfo($product);
			}elseif(isset($params['categories']) || isset($params['ages']) || isset($params['departments'])){
				// a list of products in a list of cetegory
				$categories = isset($params['categories']) ? $params['categories'] : null;
				$ages = isset($params['ages']) ? $params['ages'] : null;
				$departments = isset($params['departments']) ? $params['departments'] : null;
				
				$collection = $this->_getProductCollection($categories, $departments, $ages);
				
				
				$resultArray = array();
				foreach($collection as $product){
					$result = Mage::helper('mobileapi')->getProductInfo($product);
				    if(!!$result){
						$resultArray[] = $result;
				    }
				    
				}
				$result = $resultArray;
			}elseif(count($params) == 2 && isset($params['id']) && !!$params['id'] && isset($params['size']) && !!$params['size']){
				// get an image of a product
				$size = $params['size'];
				$productId = $params['id'];
				$product = Mage::getModel('catalog/product')->load($productId);
				
				$baseImageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product';
				switch(strtolower($size)){
					case 'small':
						$img = $baseImageUrl . $product->getData('thumbnail');
						break;
					case 'med':
						$img = $baseImageUrl . $product->getData('small_image');
						break;
					case 'large':
						$img = $baseImageUrl . $product->getData('image');
						break;
				}
				$result = $img;
				$response->setHeader('Accept', 'image/png, image/gif, image/jpeg, image/jpg', true);
			}else{
				$response->setHttpResponseCode(400); 
				Mage::throwException($this->__("Invalid Request!"));
			}
		}catch(Exception $e){
			$result = $e->getMessage();
		}
		$response->setBody(json_encode($result));
	
	}
	
	protected function _getProductCollection($categories=null, $departments=null, $ages=null)
	{
		if(!!$categories && !is_array($categories)){
			$categories = array($categories);
		}
		if(!!$ages && !is_array($ages)){
			$ages = array($ages);
		}
		if(!!$departments && !is_array($departments)){
			$departments = array($departments);
		}
		
		$categoryFilter = Mage::helper('mobileapi')->setFilter($categories[0], 'categories');
		$ageFilter = Mage::helper('mobileapi')->setFilter($ages[0], 'ages');
		$departmentFilter = Mage::helper('mobileapi')->setFilter($departments[0], 'departments');
		
		$collection = Mage::getModel('catalog/product')->getCollection()
						->addAttributeToSelect('*')
						->addAttributeToFilter($categoryFilter)
						->addAttributeToFilter($ageFilter)
						->addAttributeToFilter($departmentFilter);	
		
		return $collection;
	}
	
}