<?php

class Crown_Vouchers_Model_Order extends Mage_Core_Model_Abstract {
	
	protected 	$order, 
				$productId, 
				$orderData = array(),
				$customer; 
	
	public function createOrder($product_id) {
		$this->productId = $product_id;
		$this->init();
		$this->setOrder();
		$this->create();
	}
	
	protected function init() {
		$this->product = Mage::getModel('catalog/product')->load($this->productId);
		$this->customer = Mage::helper('customer')->getCustomer();
	}
	
	protected function setOrder() {
		$this->setInitialData();
		$this->setOrderData();
	}
	
	protected function setInitialData() {
		$this->setSessionData();
		$this->setPaymentData();
		$this->setProductData();
	}
	
	protected function setSessionData() {
		$this->orderData['session'] = array (
				'customer_id' => $this->customer->getId (), 
				'store_id' => Mage::app()->getStore()->getId() 
			);
			
		return $this;
	}
	
	protected function setPaymentData() {
		$this->orderData['payment'] = array (
				'method' => 'free' 
			);
		
		return $this;
	}
	
	protected function setProductData() {
		$this->orderData['add_products'] = array (
				$this->productId => array (
					'qty' => 1 
				) 
			);
		return $this;
	}
	
	protected function setOrderData() {
		$this->setStoreInfo();
		$this->setCustomerInfo();
		$this->setShippingInfo();
		$this->setOtherOrderInfo();
	}
	
	protected function setStoreInfo() {
		$this->orderData['order'] = array (
			'currency' => Mage::app()->getBaseCurrencyCode()
		);
		
		return $this;
	}
	
	protected function setCustomerInfo() {
		$this->setAccountInfo();
		$this->setBillingAddressInfo();
		$this->setShippingAddressInfo();
	}
	
	protected function setAccountInfo() {
		$this->orderData['order'] = array(
			'account' => array (
				'group_id' => Mage::app()->getGroup()->getId(), 
				'email' => $this->customer->getEmail() 
			)
		);
		
		return $this;
	}
	
	protected function setBillingAddressInfo() {
		
		$billingAddress = $this->customer->getDefaultBillingAddress();
		
		$this->orderData['order'] = array(
			'billing_address' => array (
				'customer_address_id' => $this->customer->getDefaultBilling (), 
				'prefix' => '', 
				'firstname' => $this->customer->getFirstname (), 
				'middlename' => '', 
				'lastname' => $this->customer->getLastname (), 
				'suffix' => '', 
				'company' => '', 
				'street' => $billingAddress->getStreet(), 
				'city' => $billingAddress->getCity (), 
				'country_id' => $billingAddress->getCountryId (), 
				'region' => $billingAddress->getRegion(), 
				'region_id' => $billingAddress->getRegionId(), 
				'postcode' => $billingAddress->getPostcode (), 
				'telephone' => $billingAddress->getTelephone (), 
				'fax' => '' 
			)
		);
		
		return $this;
	}
	
	protected function setShippingAddressInfo() {
		
		$shippingAddress = $this->customer->getDefaultShippingAddress();
		
		$this->orderData['order'] = array(
			'shipping_address' => array (
				'customer_address_id' => $this->customer->getDefaultShipping (), 
				'prefix' => '', 
				'firstname' => $this->customer->getFirstname (), 
				'middlename' => '', 
				'lastname' => $this->customer->getLastname (), 
				'suffix' => '', 
				'company' => '', 
				'street' => $shippingAddress->getStreet (), 
				'city' => $shippingAddress->getCity (), 
				'country_id' => $shippingAddress->getCountryId (), 
				'region' => $shippingAddress->getRegion(), 
				'region_id' => $shippingAddress->getRegionId (), 
				'postcode' => $shippingAddress->getPostcode (), 
				'telephone' => $shippingAddress->getTelephone (), 
				'fax' => '' 
			)
		);
		
		return $this;
	}
	
	protected function setShippingInfo() {
		$this->orderData['order'] = array(
			'shipping_method' => 'flatrate_flatrate'
		);
	}
	
	protected function setOtherOrderInfo() {
		$this->orderData['order'] = array(
			'comment' => array (
				'customer_note' => 'This order has been programmatically created via import script.' 
			), 
			'send_confirmation' => '0'
		);
	}
	
	
	
	/**
	 * Retrieve order create model
	 *
	 * @return  Mage_Adminhtml_Model_Sales_Order_Create
	 */
	protected function _getOrderCreateModel() {
		return Mage::getSingleton ( 'adminhtml/sales_order_create' );
	}
	
	/**
	 * Retrieve session object
	 *
	 * @return Mage_Adminhtml_Model_Session_Quote
	 */
	protected function _getSession() {
		return Mage::getSingleton ( 'adminhtml/session_quote' );
	}
	
	/**
	 * Initialize order creation session data
	 *
	 * @param array $data
	 * @return Mage_Adminhtml_Sales_Order_CreateController
	 */
	protected function _initSession($data) {
		/* Get/identify customer */
		if (! empty ( $data ['customer_id'] )) {
			$this->_getSession ()->setCustomerId ( ( int ) $data ['customer_id'] );
		}
		
		/* Get/identify store */
		if (! empty ( $data ['store_id'] )) {
			$this->_getSession ()->setStoreId ( ( int ) $data ['store_id'] );
		}
		
		return $this;
	}
	
	/**
	 * Creates order
	 */
	public function create() {
		$orderData = $this->orderData;
		
		if (! empty ( $orderData )) {
			
			$this->_initSession ( $orderData ['session'] );
			
			try {
				$this->_processQuote ( $orderData );
				if (! empty ( $orderData ['payment'] )) {
					$this->_getOrderCreateModel ()->setPaymentData ( $orderData ['payment'] );
					$this->_getOrderCreateModel ()->getQuote ()->getPayment ()->addData ( $orderData ['payment'] );
				}
				
				$item = $this->_getOrderCreateModel ()->getQuote ()->getItemByProduct ( $this->_product );
				
				Mage::app ()->getStore ()->setConfig ( Mage_Sales_Model_Order::XML_PATH_EMAIL_ENABLED, "0" );
				
				$_order = $this->_getOrderCreateModel ()->importPostData ( $orderData ['order'] )->createOrder ();
				
				$this->_getSession ()->clear ();
				Mage::unregister ( 'rule_data' );
				
				return $_order;
			} catch ( Exception $e ) {
				Mage::logException($e);
				Mage::log ( "Order save error..." );
			}
		}
		
		return null;
	}
	
	protected function _processQuote($data = array()) {
		/* Saving order data */
		if (! empty ( $data ['order'] )) {
			$this->_getOrderCreateModel ()->importPostData ( $data ['order'] );
		}
		
		$this->_getOrderCreateModel ()->getBillingAddress ();
		$this->_getOrderCreateModel ()->setShippingAsBilling ( true );
		
		/* Just like adding products from Magento admin grid */
		if (! empty ( $data ['add_products'] )) {
			$this->_getOrderCreateModel ()->addProducts ( $data ['add_products'] );
		}
		
		/* Collect shipping rates */
		$this->_getOrderCreateModel ()->collectShippingRates ();
		
		/* Add payment data */
		if (! empty ( $data ['payment'] )) {
			$this->_getOrderCreateModel ()->getQuote ()->getPayment ()->addData ( $data ['payment'] );
		}
		
		$this->_getOrderCreateModel ()->initRuleData ()->saveQuote ();
		
		if (! empty ( $data ['payment'] )) {
			$this->_getOrderCreateModel ()->getQuote ()->getPayment ()->addData ( $data ['payment'] );
		}
		
		return $this;
	}
	
}