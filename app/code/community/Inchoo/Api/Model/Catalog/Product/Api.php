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

class Inchoo_Api_Model_Catalog_Product_Api extends Mage_Catalog_Model_Product_Api
{
	public function info($productId, $store = null, $attributes = null, $identifierType = null)
	{
		$product = $this->_getProduct($productId, $store, $identifierType);
		$result = $this->_getProductInfo($product);
		
//		foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
//            if ($this->_isAllowedAttribute($attribute, $attributes)) {
//                $result[$attribute->getAttributeCode()] = $product->getData(
//                                                                $attribute->getAttributeCode());
//            }
//        }
		return $result;
	}
	
	/**
	 *  Retrieve a collection of all products instances
	 *  @param array $categoryList
	 */
	public function collection($categoryList, $store = null)
	{	
		if(! is_array($categoryList)){
			$categoryList = array($categoryList);
		}
	
		$collection = Mage::getModel('catalog/product')->getCollection()
						->addAttributeToSelect('*')
						->addAttributeToFilter('categories', array('in' => $categoryList));
		
		$resultArray = array();
		foreach($collection as $product){
	
			$result = $this->_getProductInfo($product);
			
			if(true){
				$resultArray[] = $result;
			}
		}
		return $resultArray;
	}
	
	/**
	 *  Get Product Detailed Info
	 *  @param $product
	 */
	protected function _getProductInfo($product)
	{
		$parentEventIds = $product->getCategoryIds();
		$link = array(
				'href'	=>	'self',
				'call'	=>	'category.info',
				'params'=>	$parentEventIds,
		);
		$event = array(
				'link'	=>	$link,
		);
		
		$result = array(
				'name'			=>	$product->getName(),
				'description'	=>	$product->getDescription(),
				'age'			=>	$product->getAges(),
				'category'		=>	$product->getCategories(),
				'color'			=>	$product->getColor(),
				'created'		=>	$product->getCreatedAt(),
				'event'			=>	$event,
				'msrp'			=>	$product->getMsrp() ? $product->getMsrp(): null,
				//'percent_discount'=>'',
				'weight'		=>	$product->getWeight(),
				'sku'			=>	$product->getSku(),
				'quantity'		=>	$product->getQty(),
				'vendor'		=>	$product->getVendor(),
				'vendor_style'	=>	$product->getVendorStyle(),
				'enable'		=>	$product->getStatus(),
		);
		
		return $result;
	}
}