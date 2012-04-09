<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license [^]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 *
 */

class Harapartners_SpeedTax_Model_Speedtax_Calculate extends Harapartners_SpeedTax_Model_Speedtax_Abstract {

	const CACHE_TTL = 120;
	const TAX_SHIPPING_LINEITEM_TAX_CLASS = 'TAX_SHIPPING';
	const TAX_SHIPPING_LINEITEM_REFERNCE_NAME = 'TAX_SHIPPING';

	protected $_speedtax = null; //This is a connection resource, no member variables, used as a singleton 
	protected $_invoice = null;
	protected $_result = null;
	
	protected $_shipFromAddress = null;
	protected $_shipToAddress = null;
	protected $_allowAddressValidation = false;
	protected $_checkoutSession;
	
	protected $_productTaxClass = null;
	protected $_productTaxClassNoneTaxableId = 0;	
	protected $_allowedCountryIds = array('US', 'CA');
	
	protected function _construct() {
		Mage::helper('speedtax')->loadSpeedTaxLibrary();
		$this->_invoice = new invoice();
		$this->_invoice->invoiceDate = date('Y-m-d');
		return parent::_construct();
	}
	
	
	
	// ================================================== //
	// ===== Entry point ================================ //
	// ================================================== //
	public function queryQuoteAddress(Mage_Sales_Model_Quote_Address $mageQuoteAddress){
		//Taxable Shipping address only, ignore billing, compatible with multiple-shipping
		if ($mageQuoteAddress->getAddressType() != Mage_Sales_Model_Quote_Address::TYPE_SHIPPING 
				|| !$this->_isTaxable($mageQuoteAddress)){
			return null;		
		}
		
		$this->_invoice->invoiceType = INVOICE_TYPES::INVOICE;
		$this->_prepareSpeedTaxInvoiceByMageAddress($mageQuoteAddress);
		if(!$this->_invoice || !$this->_invoice->lineItems){
			return null;
		}
		
		$this->_result = $this->_loadCachedResult();
		if(!$this->_result){
			$this->_result = $this->_getSpeedtax()->CalculateInvoice($this->_invoice)->CalculateInvoiceResult;
			$this->_result->_resultEvent = "Calculate Invoice"; //"Calculate Invoice" will not be logged
			$this->_updataMageQuoteItems($mageQuoteAddress);
		}
		return $this;
	}
	
	public function postQuoteAddress(Mage_Sales_Model_Quote_Address $mageQuoteAddress){
		//Taxable Shipping address only, ignore billing, compatible with multiple-shipping
		if ($mageQuoteAddress->getAddressType() != Mage_Sales_Model_Quote_Address::TYPE_SHIPPING 
				|| !$this->_isTaxable($mageQuoteAddress)){
			return null;		
		}
		
		$this->_invoice->invoiceType = INVOICE_TYPES::CREDIT;
		$this->_prepareSpeedTaxInvoiceByMageAddress($mageQuoteAddress);
		if(!$this->_invoice || !$this->_invoice->lineItems){
			return null;
		}
		
		if(!$this->_result){
			$this->_result = $this->_getSpeedtax()->CalculateInvoice($this->_invoice)->CalculateInvoiceResult;
			$this->_result->_resultEvent = "Post Invoice"; //"Calculate Invoice" will not be logged
		}
		return $this;
	}
	
	
	
	// ================================================== //
	// ===== Core Login, Line items, Cleaning Up ======== //
	// ================================================== //
	protected function _prepareSpeedTaxInvoiceByMageAddress(Mage_Sales_Model_Quote_Address $mageQuoteAddress) {
		//Clear the invoice number so that the request is just a query
		$this->_invoice->invoiceNumber = null;
		$this->_invoice->customerIdentifier = Mage::getStoreConfig ( 'speedtax/speedtax/account' ); //E.g. customer name, customer ID.  For reference only.
		
		// ----- Product line items ----- //
		//Add line items, the quote address only fetches related quote items
		foreach ( $mageQuoteAddress->getAllItems () as $mageQuoteItem ) {
			//For parent-child pair (for example: conf-simple), using parent item only
			if(!!$mageQuoteItem->getParentItemId()){
				continue;
			}
			
			//check is taxable
			if($mageQuoteItem->getTaxClassId() == $this->_productTaxClassNoneTaxableId){
				continue;
			}
			
			$sptxLineItem = new lineItem();
			$sptxLineItem->lineItemNumber = count( $this->_invoice->lineItems ); //Append to end
			//Specify tax class (product code for SpeedTax), if empty, default company code will be applied on SpeedTax's end
			if(!!$this->_getProductTaxClass()){
				$sptxLineItem->productCode = $this->_getProductTaxClass()->getOptionText($mageQuoteItem->getTaxClassId());
			}
			$sptxLineItem->customReference = $mageQuoteItem->getSku();
			$sptxLineItem->quantity = $mageQuoteItem->getQuantity ();
			$sptxPrice = new price();
			$sptxPrice->decimalValue = $mageQuoteItem->getRowTotal() - $mageQuoteItem->getDiscountAmount();
			$sptxLineItem->salesAmount = $sptxPrice;
			$sptxLineItem->shipFromAddress = $this->_getShipFromAddress ();
			$sptxLineItem->shipToAddress = $this->_getShippingToAddress ($mageQuoteAddress); //Note, address type is validated at the entry point 'queryQuoteAddress'
			
			$this->_invoice->lineItems[] = $sptxLineItem;
			//For request clean up
			$mageQuoteItem->setData('speedtax_invoice_lineitem_index', count($this->_invoice->lineItems));
		}
		
		// ----- Other line items ----- //
		//If global store config specifies: "tax_shipping", then create shipping cost line item. Note this is different from "Tax_Shipping" tax class of a product
		if(!!Mage::getStoreConfig("speedtax/speedtax/tax_shipping")){
			$this->_addLineItemFromShippingCost($mageQuoteAddress);
		}
		return $this;
	}
	
	protected function _addLineItemFromShippingCost(Mage_Sales_Model_Quote_Address $mageQuoteAddress) {
		$sptxLineItem = new lineItem();
		$sptxLineItem->lineItemNumber = count( $this->_invoice->lineItems ); //Append to end
		$sptxLineItem->productCode = self::TAX_SHIPPING_LINEITEM_TAX_CLASS;
		$sptxLineItem->customReference = self::TAX_SHIPPING_LINEITEM_REFERNCE_NAME;
		$sptxLineItem->quantity = 1;
		$sptxPrice = new price();
		$sptxPrice->decimalValue = $mageQuoteAddress->getShippingAmount ();
		$sptxLineItem->salesAmount = $sptxPrice;
		$sptxLineItem->shipFromAddress = $this->_getShipFromAddress ();
		$sptxLineItem->shipToAddress = $this->_getShippingToAddress ($mageQuoteAddress); //Note, address type is validated at the entry point 'queryQuoteAddress'
		
		$this->_invoice->lineItems [] = $sptxLineItem;
		return $this;
	}
	
	protected function _updataMageQuoteItems(Mage_Sales_Model_Quote_Address $mageQuoteAddress){
		switch ($this->_result->resultType) {
			case 'SUCCESS' :
				$this->_saveCachedResult();
				
				foreach ( $mageQuoteAddress->getAllItems() as $mageQuoteItem ) {
					if(!$mageQuoteItem->getData('speedtax_invoice_lineitem_index')){
						continue;
					}
					$taxAmount = $this->_getLineItemTaxAmountByIndex($mageQuoteItem->getData('speedtax_invoice_lineitem_index'));
					$mageQuoteItem->setTaxAmount ($taxAmount);
					$mageQuoteItem->setBaseTaxAmount ($taxAmount);
					$mageQuoteItem->setTaxPercent ( $this->getGlobalRate() * 100 ); //Result contains a global rate
				}
				
				if(!!$this->_getTaxShippingAmount()){
					$mageQuoteAddress->setShippingTaxAmount($this->_getTaxShippingAmount());
					$mageQuoteAddress->setBaseShippingTaxAmount($this->_getTaxShippingAmount());
				}
				break;
			case 'FAILED_WITH_ERRORS' || 'FAILED_INVOICE_NUMBER' :
				break;
			case 'FAILED_INVOICE_NUMBER' :
				break;
			default :
				break;
		}
		return $this;
	}
	
	// ================================================== //
	// ===== Request Cache ============================== //
	// ================================================== //
	
	// Caching based on the complete SpeedTax Invoice Object, do NOT include timestamp in the object
	// No additional timeout (timeout as session timeout) 
	protected function _loadCachedResult(){
		if(!$this->_invoice){
			return null;
		}
		try{
			$cacheKey = md5(serialize($this->_invoice));
			$speedtaxResults = $this->_getCheckoutSession()->getData( 'speedtax_results' );
			if(array_key_exists($cacheKey, $speedtaxResults)){
				$cachedResult = unserialize(gzinflate($speedtaxResults[$cacheKey]));
				if(is_object($cachedResult) && isset($cachedResult->resultType)){
					return $cachedResult;
				}
			}
		}catch(Exception $e){
		}
		return null;
	}
	
	protected function _saveCachedResult(){
		try{
			$speedtaxResults = $this->_getCheckoutSession()->getData('speedtax_results');
			$speedtaxResults[md5(serialize($this->_invoice))] = gzdeflate(serialize($this->_result), 9);
			$this->_getCheckoutSession()->setData('speedtax_results', $speedtaxResults);
		}catch(Exception $e){
			
		}
		return true;
	}
	
	// ================================================== //
	// ===== Utility Functions ========================== //
	// ================================================== //
	
	protected function _isTaxable( Mage_Sales_Model_Quote_Address $mageQuoteAddress ) {
		$originsString = Mage::getStoreConfig('speedtax/speedtax/origins');
		return in_array($mageQuoteAddress->getRegionId(), explode(',', $originsString ));
	}
	
	protected function _getCheckoutSession() {
		if( ! $this->_checkoutSession ) {
			$this->_checkoutSession = Mage::getSingleton( 'checkout/session' );
		}
		return $this->_checkoutSession;
	}
	
	protected function _getSpeedtax() {
		if(!$this->_speedtax){
			$this->_speedtax = new SpeedTax();
		}
		return $this->_speedtax;
	}
	
	//Shipping Origin Address
	protected function _getShipFromAddress() {
		if(!$this->_shipFromAddress){
			$this->_shipFromAddress = new address();
			//$countryId = Mage::getStoreConfig ( 'shipping/origin/country_id');
			$zip = Mage::getStoreConfig ('shipping/origin/postcode');
			$regionId = Mage::getStoreConfig ( 'shipping/origin/region_id');
			$state = Mage::getModel('directory/region')->load($regionId)->getName();
			$city = Mage::getStoreConfig ('shipping/origin/city');
			$street = Mage::getStoreConfig ('shipping/origin/street');
			
			$this->_shipFromAddress->address1 = $street;
			$this->_shipFromAddress->address2 = $city . ", " . $state . " " . $zip; //. ", " . $countryId;
		}
		return $this->_shipFromAddress;
	}
	
	//Shipping Destination Address
	protected function _getShippingToAddress($address) {
		if(!$this->_shipToAddress){
			$this->_shipToAddress = new address();			
			$rawAddress = new address();
			
			$country = $address->getCountry();
			$zip = $address->getPostcode(); //$zip = preg_replace('/[^0-9\-]*/', '', $address->getPostcode()); //US zip code clean up
			$state = $address->getRegion(); //No region resolution needed, $this->_getStateCodeByRegionId($address->getState());
			$city = $address->getCity();
			$street = implode(' ', $address->getStreet()); //In case of multiple line address
			
			$rawAddress->address1 = $street;
			$rawAddress->address2 = $city . ", " . $state . " " . $zip; //. ", " . $country;
			if($this->_allowAddressValidation){
				$validationResult = $this->_getSpeedtax()->ResolveAddress ( $rawAddress );
			}else{
				$validationResult = null;
			}
			if(!!$validationResult 
					&& !!$validationResult->ResolveAddressResult 
					&& !!$validationResult->ResolveAddressResult->resolvedAddress) {
				$this->_shipToAddress = $validationResult->ResolveAddressResult->resolvedAddress;
			}else{
				$this->_shipToAddress = $rawAddress;
			}
		}
		return $this->_shipToAddress;
	}
	
	protected function _getProductTaxClass(){
		if(!$this->_productTaxClass){
			$this->_productTaxClass = Mage::getModel('tax/class_source_product');
		}
		return $this->_productTaxClass;
	}
	
	

	
	// ================================================== //
	// ===== Results, Calculations ====================== //
	// ================================================== //
	public function getTotalTax() {
		return $this->_result->totalTax->decimalValue;
	}
	
	protected function _getLineItemTaxAmountByIndex($index) {
		//atternertive $result->CalculateInvoiceResult->totalTax->dollars
		try{
			if (is_array ( $this->_result->lineItemBundles->lineItems )) {
				return $this->_result->lineItemBundles->lineItems[$index]->taxAmount->decimalValue;
			} else {
				return $this->_result->lineItemBundles->lineItems->taxAmount->decimalValue;
			}
		}catch(Exception $e){
			return 0;
		}
	}

	protected function _getTaxShippingAmount() {
		if (is_array($this->_result->lineItemBundles->lineItems)) {
			$lineItems = $this->_result->lineItemBundles->lineItems;
			foreach ( $lineItems as $item ) {
				if ($item->productCode == self::TAX_SHIPPING_LINEITEM_TAX_CLASS) {
					return $item->taxAmount->decimalValue;
				}
			}
		} else {
			if ($this->_result->lineItemBundles->lineItems->productCode == self::TAX_SHIPPING_LINEITEM_TAX_CLASS) {
				return $this->_result->lineItemBundles->lineItems->taxAmount->decimalValue; 
			}
		}
		return 0.0;
	}

	
	
	// ================================================== //
	// ===== Log ======================================== //
	// ================================================== //
	protected function _makeLog() {
		$log = array ();
		if ($this->_result->resultType != "SUCCESS") {
			//Error Log
			$log ['event'] = $this->_result->_resultEvent;
			$log ['result_type'] = $this->_result->errors->type;
			$log ['message'] = $this->_result->errors->message;
			$fromAddress = $this->_invoice->lineItems [0]->shipFromAddress;
			$log ['address_shipping_from'] = $fromAddress->address1 . " " . $fromAddress->address2;
			$toAddress = $this->_invoice->lineItems [0]->shipToAddress;
			$log ['address_shipping_to'] = $toAddress->address1 . " " . $toAddress->address2;
			$log ['customer_name'] = $this->_invoice->customerIdentifier;
			$log ["error"] = true;
		}
		if ($this->_result->_resultEvent == "Post Invoice" 
				|| $this->_result->_resultEvent == "Pending Credit" 
				|| $this->_result->_resultEvent == "Pending Invoice"
		) {
			//API Request Log
			$log ['event'] = $this->_result->_resultEvent;
			$log ['result_type'] = $this->_result->resultType;
			$log ['invoice_num'] = $this->_invoice->invoiceNumber;
			if ($this->_result->_resultEvent == "Post Invoice") {
				$log ['gross'] = $this->_invoice->subtotal;
				$log ['exempt'] = $this->_invoice->exempt;
				$log ['tax'] = $this->_invoice->tax;
			}
			if ($this->_result->_resultEvent == "Pending Invoice" || $this->_result->_resultEvent == "Pending Credit") {
				$log ['gross'] = $this->_result->totalSales->decimalValue;
				$log ['exempt'] = $this->_result->totalExmptSales->decimalValue;
				$log ['tax'] = $this->_result->totalTax->decimalValue;
			}
			$log ["call"] = true;
		}
		return $log;
	}

}