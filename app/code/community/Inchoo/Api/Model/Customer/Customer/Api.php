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

class Inchoo_Api_Model_Customer_Customer_Api extends Mage_Customer_Model_Customer_Api
{   
  	public function __construct()
  	{
 		$this->baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);	
  	}
	
	public function auth($username, $password)
	{   
		
		$customer = Mage::getModel('customer/customer')
            ->setWebsiteId(1);
        if ($customer->authenticate($username, $password)) {
        	//$session = Mage::getModel('customer/session');
            //$session->setCustomerAsLoggedIn($customer);
            //$session->renewSession();
            return true;
        }
        return false;
	}
	
//	public function logout()
//	{
//		$session = getModel('customer/session');
//	}
	
	public function info($customerId, $attributes = null)
	{
		$customer = Mage::getModel('customer/customer')->load($customerId);

        if (!$customer->getId()) {
            $this->_fault('not_exists');
        }

        if (!is_null($attributes) && !is_array($attributes)) {
            $attributes = array($attributes);
        }

        $defaultBilling = $customer->getDefaultBillingAddress();
		$defaultShipping = $customer->getDefaultShippingAddress();
		$defaultShippingId = $defaultShipping ? $defaultShipping->getId() : null;
        $defaultBillingId = $defaultBilling ? $defaultBilling->getId() : null;
        
		$shipping_link = array(
        	'rel'	=> 	'shipping address',
			'href'	=>	$this->baseUrl . 'address/' . $defaultShippingId,
        	//'call'	=> 'customer_address.info',
        	//'params' =>	$defaultShipping ? $defaultShipping->getId() : null,
        );
        $billing_link = array(
        	'rel'	=>	'billing address',
        	'href'	=>	$this->baseUrl . 'address/' . $defaultBillingId,
        	//'call'	=>	'customer_address.info',
        	//'params'	=>  $defaultBilling ? $defaultBilling->getId(): null,
        );
        $address_links = array($shipping_link, $billing_link);
        
        $link = array(
        	'ref'	=>	'self',
        	'href'	=>	$this->baseUrl . 'user/id/' . $customerId,
        );
        
        $result = array(
        		'id'		=>	$customerId,
        		'email'		=>	$customer->getEmail(),
        		'firstname'	=>	$customer->getFirstname(),
        		'lastname'	=>	$customer->getLastname(),
        		'addresses'	=>	$address_links,
        		'link'		=>	$link
        		
        );
        
        return $result;
	}
	
	public function address($customerId)
	{   
		$customer = Mage::getModel('customer/customer')->load($customerId);
		
        if (!$customer->getId()) {
            $this->_fault('not_exists');
        }
       	$result = array();
        
        $addressIds = $customer->getPrimaryAddressIds();
      
        foreach($addressIds as $Id){
        	$result[] = $this->_getAddress($Id);
        }
        return $result;
	}
	/**
	 *  Get Cart Info, similiar to cart.info, which get quote from quote id
	 *  this one get quote from session 
	 */
	public function cart($customerId){
		$customer = Mage::getModel('customer/customer')->load($customerId);
		$quote = Mage::getModel('sales/quote')
                ->setStoreId(1);
        
        
		//$quote = $this->_getCart()->getQuote();
		$quote->LoadByCustomer($customer);
		
		if ($quote->getGiftMessageId() > 0) {
            $quote->setGiftMessage(
                Mage::getSingleton('giftmessage/message')->load($quote->getGiftMessageId())->getMessage()
            );
        }
        $user_link = array(
        		'rel'	=>	'self',
        		'href'	=>	$this->baseUrl . 'user/id/' . $quote->getCustomerId(),
				//'call'	=>	'customer.info',
				//'params'=>	$quote->getCustomerId(),
		);
		
		$user = array(
				'link'	=>	$user_link,
		);
		
		$link = array(
				'rel'	=>	'self',
				'call'	=>	'cart.info',
				'params'=>	$quote->getId(),
		);
		$items= array();
		foreach($quote->getAllItems() as $item){
			$itemInfo = array(
				'item_id'	=>	$item->getId(),
				'name'		=>	$item->getName(),
				'quantity'	=>	$item->getQty(),
				'link'		=>	array(
									'rel'	=>	'self',
									'href'	=>	$this->baseUrl . 'product/id/' . $item->getProductId(),
									//'call'	=>	'catalog_product.info',
									//'params'=>	$item->getProductId(),
								),
			);
			$items[] = $itemInfo;
		}
		
		$result = array(
				'user'	=>	$user,
				'items'	=>	$items,
				'link'	=>  $link,
		);
		return $result;
	}
	
	/**
	 *  Get Address Info
	 *  @param int $addressId
	 */
	protected function _getAddress($addressId)
	{
		$address = Mage::getModel('customer/address')->load($addressId);
		
        if (!$address->getId()) {
            $this->_fault('not_exists');
        }
        
        $result = array(
        	//'type'		=>	'',
        	'firstname'	=>	$address->getFirstname(),
        	'lastname'	=>	$address->getLastname(),
        	'address'	=>	array($address->getStreet(1), $address->getStreet(2), $address->getStreet(3)),
        	'region'	=>	$address->getRegion(),
        	'country'	=>	$address->getCountry(),
        	'zip'		=>	$address->getPostcode(),
        );
        return $result;
	}
	
	protected function _getCart()
	{
		return Mage::getSingleton('checkout/cart');
	}
}