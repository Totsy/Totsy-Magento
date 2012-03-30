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

class Harapartners_MobileApi_Helper_Data extends Mage_Core_Helper_Url {
	
	public function __construct()
  	{
 		$this->baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'mobileapi';	
  	}
  	
  	public function getCcInfo($ccInfo)
  	{
  		$customer = Mage::getModel('customer/customer')->load($ccInfo->getData('cust_id'));
  		$expire_date = $ccInfo->getData('expire_date');
  		$expire_date = explode('-', $expire_date);
  		$expire_year = $expire_date[0];
  		$expire_month = $expire_date[1];
  		$result = array(
  			'type' 				=> 	$ccInfo->getData('card_type'),
  			'last_four'			=>	$ccInfo->getData('last4no'),
  			'expire_year'		=>	$expire_year,
  			'expire_month'		=>	$expire_month,
  			'cardholder_name'	=>	$customer->getFirstname() . ' ' . $customer->getLastname(),
  			'links'				=>	array(
  										'rel'	=>	$this->baseUrl,
  										'href'	=>	$this->baseUrl . '/user/' . $customer->getId(),
  									),
  		);
  		return $result;
  	}
  	
  	public function getRewardInfo($reward)
  	{   
  		
  		$links = array(
  				'rel'	=>	$this->baseUrl . '/user',
  				'href'	=>	$this->baseUrl . '/user/' . $reward->getCustomerId(),
  		);
  		$result = array(
  			'description'	=>	'',
  			'amount'		=>	$reward->getData('points_balance'),
  			'expires'		=>	'',
  			'links'			=>	$links,
  		);
  		
  		return $result;
  	}
	public function getProductInfo($product)
	{
		$parentEventIds = $product->getCategoryIds();
		$links = array();
		
		if(!! $parentEventIds && !is_array($parentEventIds)){
			$parentEventIds = array($parentEventIds);
		
			foreach($parentEventIds as $eventId){
				$links[] = array(
					'rel'	=>	$this->baseUrl . '/event',
					'href'=>	$this->baseUrl . '/event/' . $eventId,
				);
			}
		}
		
		$result = array(
				'name'			=>	$product->getName(),
				'description'	=>	$product->getDescription(),
				'age'			=>	$product->getAges(),
				'category'		=>	$product->getCategories(),
				'color'			=>	$product->getColor(),
				'created'		=>	$product->getCreatedAt(),
				//'event'			=>	$event,
				'msrp'			=>	$product->getMsrp() ? $product->getMsrp(): null,
				//'percent_discount'=>'',
				'weight'		=>	$product->getWeight(),
				'sku'			=>	$product->getSku(),
				'quantity'		=>	$product->getQty(),
				'vendor'		=>	$product->getVendor(),
				'vendor_style'	=>	$product->getVendorStyle(),
				'enabled'		=>	$product->getStatus(),
				'links'			=>  $links,
		);
		
		return $result;
	}
	
	public function getEventInfo($eventId)
	{
		$event = Mage::getModel('catalog/category')->load($eventId);
       
        if(!!$event->getId()){
			$collection = $event->getProductCollection();
			$links = array();
			$items = array();
			$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB); 
			
			foreach($collection as $product){
				$items[] = array(
						'rel'		=>	'related',
						'href'		=>	$this->baseUrl . '/product/' . $product->getId(),
				);
			}
			$link = array(
					'rel'		=>	'self',
					'href'		=>	$this->baseUrl . '/event/' . $eventId,
			);
				
			$result = array(
					'name'		=>	$event->getName(),
					'blurb'		=>	$event->getDescription(),
					'start'		=>	$event->getEventStartDate(),
					'end'		=>	$event->getEventEndDate(),
					'items'		=>	$items,
					'link'		=>	$link,
					'enabled'	=>	$event->getIsActive(),
					'departments' => $event->getDepartments(),
			);
       	}else{
       		Mage::throwException($this->__('Event Does not exist'));
       	}
        
		return $result;
	}
	
	public function getUserInfo($customer)
	{   
		$customerId = $customer->getId();
		$defaultBilling = $customer->getDefaultBillingAddress();
		$defaultShipping = $customer->getDefaultShippingAddress();
		$defaultShippingId = $defaultShipping ? $defaultShipping->getId() : null;
        $defaultBillingId = $defaultBilling ? $defaultBilling->getId() : null;
        
//		$shipping_link = array(
//        	'rel'	=> 	'shipping address',
//			'href'	=>	$this->baseUrl . '/user/' . $defaultShippingId . '/address',	
//        );
//        $billing_link = array(
//        	'rel'	=>	'billing address',
//        	'href'	=>	$this->baseUrl . '/user/' . $defaultBillingId . '/address',
//        );
//        $address_links = array($shipping_link, $billing_link);
//        
		$links = array();
        $links[] = array(
        			'ref'	=>	$this->baseUrl . '/address',
        			'href'	=>	$this->baseUrl . '/user/' . $customerId . '/address',
        );
        
        $links[] = array(
        			'ref'	=>	$this->baseUrl . '/order',
        			'href'	=>	$this->baseUrl . '/user/' . $customerId . '/order',
        );
        
        $links[] = array(
        			'ref'	=>	$this->baseUrl . '/reward',
        			'href'	=>	$this->baseUrl . '/user/' . $customerId . '/reward',
        );
        $result = array(
        		'id'			=>	$customerId,
        		'email'			=>	$customer->getEmail(),
        		'firstname'		=>	$customer->getFirstname(),
        		'lastname'		=>	$customer->getLastname(),
        		'password_hash'	=>	$customer->getPasswordHash(),
        		//'addresses'	=>	$address_links,
        		'links'		=>	$links,
        		
        );
        
        return $result;
	}
	
	public function getAddressInfo($address)
	{
		$result = array(
        	//'type'		=>	'',
        	'firstname'	=>	$address->getFirstname(),
        	'lastname'	=>	$address->getLastname(),
        	'address'	=>	array($address->getStreet(1), $address->getStreet(2), $address->getStreet(3)),
			'city'		=>	$address->getCity(),
        	'region'	=>	$address->getRegion(),
        	'country'	=>	$address->getCountry(),
        	'zip'		=>	$address->getPostcode(),
        );
        return $result;
	}
	
	public function getUserAddressesInfo($user)
	{
		$addresses = $user->getAddresses();
		$result = array();
		
		foreach($addresses as $address){
			$result[] = array(
				'firstname'	=>	$address->getFirstname(),
      	  		'lastname'	=>	$address->getLastname(),
        		'address'	=>	array($address->getStreet(1), $address->getStreet(2), $address->getStreet(3)),
        		'region'	=>	$address->getRegion(),
        		'country'	=>	$address->getCountry(),
        		'zip'		=>	$address->getPostcode(),
				'link'		=>	array(
									'ref' 	=>	'self',
									'href'	=>	$this->baseUrl . '/address/' . $address->getId(),
								),
			);
		}
		return $result;
	}
	
	public function setFilter($attributeList, $attributeName)
	{	
		$filter = array();
		if(!! $attributeList){
			$attributeArray = explode(',', $attributeList);
			foreach($attributeArray as $key => $value){
				$filter[] = array('attribute' => $attributeName, 'like' => '%'.$value.'%');
			}	
		}else{
			$filter[] = array('attribute' => $attributeName, 'notnull' => 1);
		}
		return $filter;
	}	 
	
	public function getSession()
	{
		return $this->_getSession();
	}
	
	protected function _getSession()
	{
		return Mage::getSingleton('customer/session');
	}
	
	public function getCart()
	{
		return $this->_getCart();
	}
	
	protected function _getCart()
	{
		return Mage::getSingleton('checkout/cart');
	}
	
	
	public function setApiKey($user_email, $password, $user_id){
		
		return sha1($user_email . $passoword . $user_id);
		
//		$hash = sha1(mt_rand());
//		$consumer_key = substr($hash, 0, 30);   	// api key
//		$consumer_secret = substr($hash, 30, 10);	// user_token
	}
	
	public function setUserToken($userEmail, $user_id){
		
		return sha1($userEmail . $user_id);
	}
	
//	public function login($userEmail, $password){
//		$customer = Mage::getModel('customer/customer');
//		$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
//		$auth = $customer->authenticate($userEmail, $password);
//	 
//        if ($auth) {
//            $this->_getSession()->setCustomerAsLoggedIn($customer);
//            $this->_getSession()->renewSession();
//            return $this->_getSession()->getSessionId();
//        }
//        return false;
//	}
	
	/**
     * End web service session
     *
     * @param string $sessionId
     * @return boolean
     */
//	public function logout($sessionId){
//		
//		$this->_getSession()->setSessionId($sessionId);
//		$this->_getSession()->clear();
//		var_dump($this->_getSession()->getSessionId());
//		return true;
//	}
	
	public function authenticateAccount($account_id, $user_auth_token){
		
		$auth = $this->verifyToken($user_auth_token, $account_id);
		return $auth;
	}
	
	protected function verifyToken($user_auth_token, $account_id){
		
		$customerEmail = Mage::getModel('customer/customer')->load($account_id)->getEmail();
		$token = $this->setUserToken($customerEmail, $account_id);
		if($token == $user_auth_token){
			return true;
		}
		return false;
	}
}