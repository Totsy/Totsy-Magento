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

	protected $_lines = null;
	protected $_invoice = null;
	
	protected $_result = null;
//	protected $_resultType; ??
	
	protected $_tax;
	protected $_rate;
	
	protected $_checkoutSession;
	
	protected $_taxClassMapProductCodeArray = array();
	protected $_taxClassIdMapNameArray = array();
	
	protected function _construct() {
		Mage::helper ( 'speedtax' )->loadSpeedTaxLibrary ();
		$this->_invoice = new invoice ( );
		$this->_invoice->invoiceDate = date ( 'Y-m-d' );
		$this->_invoice->invoiceType = INVOICE_TYPES::INVOICE;
		
		$taxClasses = Mage::getModel( 'tax/class' )->getCollection();
		foreach( $taxClasses as $taxClass ) {
			$this->_taxClassIdMapNameArray[ $taxClass->getClassId() ] = $taxClass->getClassName();
		}
		
		return parent::_construct ();
	}
	
	// ========== Entry point ========== //
	//for collect total, billing vs. shipping vs. multi-shipping
	public function queryQuoteAddress(Mage_Sales_Model_Quote_Address $mageQuoteAddress){
		//Shipping address only!
		if ($address->getAddressType () != Mage_Sales_Model_Quote_Address::TYPE_SHIPPING 
				|| !Mage::getStoreConfig("speedtax/speedtax/tax_shipping", $store->getId())) {
			return null;		
		}
		
		$cacheKey = $this->_prepareSpeedTaxInvoiceByMageAddres($mageQuoteAddress);
		//nothing to calculate!
		if(!$this->hasItem()){
			return null;
		}
		
		$this->_result = $this->_loadCachedResult( $cacheKey );
		if(!$this->_result){
			$this->_result = $stx->CalculateInvoice ( $this->_invoice )->CalculateInvoiceResult;
			$this->_result->_resultEvent = "QueryInvoice";
			
			if($this->_result->resultType == 'SUCCESS'){
				$this->_saveCachedResult($cacheKey, $this->_result);
			}
		}
		
		//TODO: result processing
//		return $this->_resultHandler ();
		return $this;
	}
	
	protected function _prepareSpeedTaxInvoiceByMageAddress(Mage_Sales_Model_Quote_Address $mageQuoteAddress) {
		//Clear the invoice number so that the request is just a query
		$this->_invoice->invoiceNumber = null;
		$this->_invoice->customerIdentifier = Mage::getStoreConfig ( 'speedtax/speedtax/account' ); //E.g. customer name, customer ID.  For reference only.
	
		//Add line items
		foreach ( $mageQuoteAddress->getAllItems () as $mageQuoteAddressItem ) {
			//not all items are equal!
			//not all items are treated equal, conf-simple pair etc, some are not taxable as well
			//Using parent item only
			if(!!$mageQuoteAddressItem->getParentItemId()){
				continue;
			}
			//check is taxable??
			if(false){
				continue;
			}
			
			$this->_addLineItemFromAddressItem( $mageQuoteAddressItem );
		}

		//If tax shipping
		if(false){
			$this->_addLineItemFromShippingCost($mageQuoteAddress);
		}

		return $this;
	}
	
	
	
	protected function _addLineItemFromAddressItem(Mage_Sales_Model_Quote_Address_Item $mageQuoteAddressItem){
		$product = $mageQuoteAddressItem->getProduct();

		$sptxLineItem = new lineItem();
		$sptxLineItem->lineItemNumber = count( $this->_invoice->lineItems ); //Append to end
		
		$sptxLineItem->productCode = $this->_productCodeLookUpByTaxClass($product);
		$sptxLineItem->customReference = "Reference Info";
		$sptxLineItem->quantity = $mageQuoteAddressItem->getQuantity ();
		
		$sptxPrice = new price();
		$sptxPrice->decimalValue = $mageQuoteAddressItem->getRowTotal() - $mageQuoteAddressItem->getDiscountAmount();
		$sptxLineItem->salesAmount = $sptxPrice;

		$sptxLineItem->shipFromAddress = $this->_getShippingFromAddress ();
		$sptxLineItem->shipToAddress = $this->_getShippingToAdress ( $mageQuoteAddressItem->getAddress()); //Note, address type is validated at the entry point 'queryQuoteAddress'
		
		$this->_invoice->lineItems [] = $sptxLineItem;
		return $this;
	}
	
	protected function _addLineItemFromShippingCost(Mage_Sales_Model_Quote_Address $mageQuoteAddress) {
		$store = Mage::app()->getStore();
		$shippingCostObject = new Varien_Object ( );
		$shippingCostObject->setId ( Mage::getStoreConfig ( 'speedtax/speedtax/shipping_sku', $store->getId()));
		$shippingCostObject->setQuote ( $mageQuoteAddress->getQuote () );

		$quote = $shippingCostObject->getQuote ();
		
		//$stx = new SpeedTax ( );
		

		$t = explode ( ' ', microtime () );
		$InvoiceNr = "INV" . $t [1];
		
		$this->_invoice->invoiceNumber = $InvoiceNr; //This is your invoice number for this purchase.  Leave this out for a straight query.
		$sptxPrice = new price ( );
		$sptxPrice->decimalValue = ( float ) $quote->getShippingAddress ()->getShippingAmount ();
		$sptxLineItem = new lineItem ( );
		$sptxLineItem->lineItemNumber = count( $this->_invoice->lineItems );
		$sptxLineItem->productCode = $shippingCostObject->getId ();
		$sptxLineItem->customReference = "Shipping";
		$sptxLineItem->quantity = 1;
		$sptxLineItem->salesAmount = $sptxPrice;
		
		/**********get shipp from address from config setting ***********/
		$sptxLineItem->shipFromAddress = $this->_getShippingFromAddress ();
		
		$shippingAddress = ($quote->getShippingAddress ()->getPostcode ()) ? $quote->getShippingAddress () : $quote->getBillingAddress ();
		$ShipToAddress = $this->_getShippingToAdress ( $shippingAddress );
		
		$sptxLineItem->shipToAddress = $ShipToAddress;
		
		$this->_invoice->lineItems [] = $sptxLineItem;
		
		return true;
	}
	
	
//get Departure Shipping Address
	protected function _getShippingFromAddress() {
		$ShipFromAddress = new address ( );
		$country = Mage::getStoreConfig ( 'shipping/origin/country_id', $store );
		$zip = Mage::getStoreConfig ( 'shipping/origin/postcode', $store );
		$regionId = Mage::getStoreConfig ( 'shipping/origin/region_id', $store );
		$state = Mage::getModel ( 'directory/region' )->load ( $regionId )->getCode ();
		$city = Mage::getStoreConfig ( 'shipping/origin/city', $store );
		$street = Mage::getStoreConfig ( 'shipping/origin/street', $store );
		
		$ShipFromAddress->address1 = $street;
		$ShipFromAddress->address2 = $city . ", " . $state . " " . $zip; //. ", " . $country;
		return $ShipFromAddress;
	}
	
	//get Destination Shipping Address
	protected function _getShippingToAdress($address) {
		$stx = new SpeedTax ( );
		$rawAddress = new address ( );
		$ShipToAddress = new address ( );
		$country = $address->getCountry ();
		$zip = $zip = preg_replace ( '/[^0-9\-]*/', '', $address->getPostcode () );
		//$regionId = 
		$state = Mage::getModel ( 'directory/region' )->load ( $address->getRegionId () )->getCode ();
		$city = $address->getCity ();
		$streetArr = $address->getStreet ();
		$street = $streetArr [0] . " " . $streetArr [1];
		
		$rawAddress->address1 = $street;
		$rawAddress->address2 = $city . ", " . $state . " " . $zip; //. ", " . $country;
		//address validation
		$result = $stx->ResolveAddress ( $rawAddress );
		$fullAddress = $result->ResolveAddressResult->resolvedAddress;
		
		$ShipToAddress->address1 = $fullAddress->address;
		$ShipToAddress->address2 = $fullAddress->city . ", " . $fullAddress->state . " " . $fullAddress->zip; //. ", " . $country;

		return $ShipToAddress;
	}
	
	
	//product sku is the default
	protected function _productCodeLookUpByTaxClass( $product ) {
		if( Mage::getStoreConfig( 'speedtax/speedtax/customized_tax_class' ) ) {
			$taxClassId = $product->getTaxClassId();
			if( isset( $this->_taxClassIdMapNameArray[ $taxClassId ] ) ) {
				$taxClassName = $this->_taxClassIdMapNameArray[ $taxClassId ];
				if( isset( $this->_taxClassMapProductCodeArray[ $taxClassName ] ) ) {
					return $this->_taxClassMapProductCodeArray[ $taxClassName ];
				}
			}
		}
		return $product->getSku();
	}
	
	protected function _resultHandler() {
		
		switch ($this->_result->resultType) {
			case 'SUCCESS' :
				return true;
				break;
			case 'FAILED_WITH_ERRORS' || 'FAILED_INVOICE_NUMBER' :
				//log hangdler;
				return false;
				break;
			case 'FAILED_INVOICE_NUMBER' :
				return false;
				break;
			//print "FAILED. The invoice number is incorrectly formatted.\n";
			default :
				return false;
				break;
			//print "Other result type: '" . $this->_result->CalculateInvoiceResult->resultType . "'\n";
		}
	
	}
	
//	//$stx->CalculateInvoice when invoice number missing, just query
//	//With invoice number, create an invoice and status 'Pending'
//	$stx->CalculateInvoice ( $this->_invoice ); //Query
//	$stx->CalculateInvoice ( $this->_invoice ); //Pending
//	$stx->CalculateInvoice ( $invoiceNumbers ); //Post
//	
//	//To post an invoice
//	$stx->PostInvoice
//	//Same as $stx->CalculateInvoice, but invoice number required, and status 'Posted'
//	
//	Key question: line items should be simple or configurable item??
//	Simple product is better, but we need the info from the configurable product
	
	
	
	// ============================================ //
	// ============ Utility functions ============= //
	// ============================================ //
	
	/**
	 * Check if store has nexus inside destination state
	 * @param address Mage_Sales_Model_Quote_Address
	 * @return boolean
	 */
	public function isTaxable( $address ) {
		$originsString = Mage::getStoreConfig( 'speedtax/speedtax/origins' );
		$originsArray = explode( ',', $originsString ); 
		foreach( $originsArray as $origin ) {
			if( $address->getRegionId() == $origin ) {
				return true;
			}
		}
		return false;
	}
	
	
	
	protected function _getCheckoutSession() {
		if( ! $this->_checkoutSession ) {
			$this->_checkoutSession = Mage::getSingleton( 'checkout/session' );
		}
		return $this->_checkoutSession;
	}
	
	protected function _getCachedResult( $invoice ) {
		$speedtaxInvoices = $this->_getCheckoutSession()->getData( 'speedtax_invoices' );
		foreach( $speedtaxInvoices as $speedtaxInvoice ) {
			$preparedInvoice = unserialize( $speedtaxInvoice );
			if( isset( $preparedInvoice[ 'invoice' ] ) && $preparedInvoice[ 'invoice' ] == $invoice ) {
				return $preparedInvoice[ 'result' ];
			}
		}
		return false;
	}
	
	//prepare for QueryTax(), add every item in the order
	public function addLine($mageItem) {
		if (!$mageItem) {
			return false;
		}
		
		if($mageItem instanceof Mage_Sales_Model_Quote_Item){
			return $this->_addLineItemFromQuoteItem($mageItem);
		}
		
		if($mageItem instanceof Mage_Sales_Model_Order_Item){
			return $this->_addLineItemFromOrderItem($mageItem);
		}
		
		return false;
	}
	
	//add shipping cost as an item to speedtax request
	public function addShipping($mageItem) {
		if (! $mageItem) {
			return false;
		}
		$quote = $mageItem->getQuote ();
		
		//$stx = new SpeedTax ( );
		

		$t = explode ( ' ', microtime () );
		$InvoiceNr = "INV" . $t [1];
		
		$this->_invoice->invoiceNumber = $InvoiceNr; //This is your invoice number for this purchase.  Leave this out for a straight query.
		$sptxPrice = new price ( );
		$sptxPrice->decimalValue = ( float ) $quote->getShippingAddress ()->getShippingAmount ();
		$sptxLineItem = new lineItem ( );
		$sptxLineItem->lineItemNumber = count( $this->_invoice->lineItems );
		$sptxLineItem->productCode = $mageItem->getId ();
		$sptxLineItem->customReference = "Shipping";
		$sptxLineItem->quantity = 1;
		$sptxLineItem->salesAmount = $sptxPrice;
		
		/**********get shipp from address from config setting ***********/
		$sptxLineItem->shipFromAddress = $this->_getShippingFromAddress ();
		
		$shippingAddress = ($quote->getShippingAddress ()->getPostcode ()) ? $quote->getShippingAddress () : $quote->getBillingAddress ();
		$ShipToAddress = $this->_getShippingToAdress ( $shippingAddress );
		
		$sptxLineItem->shipToAddress = $ShipToAddress;
		
		$this->_invoice->lineItems [] = $sptxLineItem;
		
		return true;
	}
	
	//prepare for invoiceTaxPending(), total price is needed, no specific items preparation here 
	public function addOrder($order) {
		if (! $order) {
			return false;
		}
		
		//$stx = new SpeedTax ( );
		$this->_invoice->invoiceNumber = $order->getData ( "increment_id" );
		
		$sptxPrice = new price ( );
		if ($order->getData ( "IsCreditMemo" )) {
			$sptxPrice->decimalValue = $order->getSubtotalRefunded ();
		} else {
			$sptxPrice->decimalValue = $order->getSubtotal ();
		}
		$sptxLineItem = new lineItem ( );
		//$sptxLineItem->lineItemNumber = count( $this->_invoice->lineItems );
		$sptxLineItem->productCode = "product_sku"; //$observer->getInvoice()->getOrder()->getAllItems();
		$sptxLineItem->customReference = "My Custom Reference Info";
		$sptxLineItem->quantity = 1;
		$sptxLineItem->salesAmount = $sptxPrice;
		
		/**********get shipp from address from config setting ***********/
		$sptxLineItem->shipFromAddress = $this->_getShippingFromAddress ();
		
		$shippingAddress = ($order->getIsVirtual()) ? $order->getBillingAddress () : $order->getShippingAddress () ;
		$ShipToAddress = $this->_getShippingToAdress ( $shippingAddress );
		
		$sptxLineItem->shipToAddress = $ShipToAddress;
		
		$this->_invoice->lineItems [] = $sptxLineItem;
		
		return true;
	}
	
	//prepare for InvoiceTaxPost(), only invoice number is needed
	public function addInvoice($invoice) {
		if (! $invoice) {
			return false;
		}
		
		//$stx = new SpeedTax ( );
		$this->_invoice->invoiceNumber = $invoice->getData ( "increment_id" );
		$this->_invoice->subtotal = $invoice->getSubtotal ();
		$this->_invoice->tax = $invoice->getTaxAmount ();
		$this->_invoice->exempt = $invoice->getExempt ();
		
		if(!!$invoice->getOrder()){
			$this->addOrder ( $invoice->getOrder() );
		}
		
		return true;
	}
	
	//prepare for invoiceTaxPending, 
	public function addCreditmemo($order) {
		if (! $order) {
			return false;
		}
		$order->setData ( "IsCreditMemo", true );
		$this->_invoice->invoiceType = INVOICE_TYPES::CREDIT;
		$this->addOrder ( $order );
		$this->_invoice->invoiceNumber = $order->getData ( "increment_id" ) . "CR1";
		
		//$this->_invoice->subtotal =  $order->getSubtotalRefunded();
		//$this->_invoice->tax = $order->getTaxRefunded();
		//$this->_invoice->exempt = [I dont know where to get];
		return true;
	}
	
	//Sending Invoice, status pending on speedtax
	public function invoiceTaxPending() {
		
		$this->_invoice->customerIdentifier = Mage::getStoreConfig ( 'speedtax/speedtax/account' ); //E.g. customer name, customer ID.  For reference only.	
		

		$stx = new SpeedTax ( );
		if ($this->_invoice) {
			$this->_result = $stx->CalculateInvoice ( $this->_invoice )->CalculateInvoiceResult;
			if ($this->_invoice->invoiceType == INVOICE_TYPES::CREDIT) {
				$this->_result->_resultEvent = "Pending Credit";
			} else {
				$this->_result->_resultEvent = "Pending Invoice";
			}
			$log = $this->_makeLog ();
			$LogModel = Mage::getModel ( 'speedtax/log' );
			$LogModel->log ( $log );
		}
		return $this->_resultHandler ();
	}
	
	//switch pending invoices to post status on speedtax
	public function invoiceTaxPost() {
		$this->_invoice->customerIdentifier = Mage::getStoreConfig ( 'speedtax/speedtax/account' ); //E.g. customer name, customer ID.  For reference only.	

		$stx = new SpeedTax ( );
		if ($this->_invoice) {
			$invoiceNumbers [0] = $this->_invoice->invoiceNumber;
			$this->_result = $stx->CalculateInvoice ( $invoiceNumbers )->PostBatchInvoicesResult;
			$this->_result->_resultEvent = "Post Invoice";
			$log = $this->_makeLog ();
			$LogModel = Mage::getModel ( 'speedtax/log' );
			$LogModel->log ( $log );
		}
		return $this->_resultHandler ();
	}
	
	
	
	public function getTotalTax() {
		if (! $this->_tax) {
			//atternertive $result->CalculateInvoiceResult->totalTax->dollars
			return $this->_tax = $this->_result->totalTax->decimalValue;
		}
		return $this->_tax;
	
	}
	
	public function getTotalRate() {
		if (! $this->_rate) {
			if(is_array( $this->_result->lineItemBundles->taxes)){//result from speedtax, if multiple item(including shipping tax), then it will be array
				$TotalRate = 0;
				$taxes = $this->_result->lineItemBundles->taxes;
				foreach ($taxes as $tax){
					$TotalRate += $tax->effectiveRate; 
				}
				$this->_rate = $TotalRate;
			}elseif ($this->_result->lineItemBundles->taxes->effectiveRate){
				$this->_rate = $this->_result->lineItemBundles->taxes->effectiveRate;
			}
		}
		return $this->_rate;
	}
	
	//get Nth item's tax
	public function getTax($index) {
		
		//atternertive $result->CalculateInvoiceResult->totalTax->dollars
		if (is_array ( $this->_result->lineItemBundles->lineItems )) {
			return $this->_result->lineItemBundles->lineItems [$index]->taxAmount->decimalValue;
		} else {
			return $this->_result->lineItemBundles->lineItems->taxAmount->decimalValue;
		}
	
	}
	
	//
	public function getShippingTax() {
		if (is_array ( $this->_result->lineItemBundles->lineItems )) {//result from speedtax, if multiple item(including shipping tax), then it will be array
			$lineItems = $this->_result->lineItemBundles->lineItems;
			foreach ( $lineItems as $item ) {
				if ($item ->productCode == "Shipping") {
					return $item->taxAmount->decimalValue;
				}
			}
		} else {
			if ($this->_result->lineItemBundles->lineItems ->productCode == "Shipping") {
				return $this->_result->lineItemBundles->lineItems->taxAmount->decimalValue; 
			}
		}
		return 0;
	}
	
	//just return the total rate, which is the same with every item
	public function getRate($index) {
		
		return $this->_rate;
	
	}
	
	public function hasItem() {
		if (! $this->_invoice->lineItems) {
			return false;
		}
		return true;
	}
	public function hasResult() {
		if (empty ( $this->_result )) {
			return false;
		}
		return true;
	}
	
	/**
	 * Generates a hash key for the exact request
	 *
	 * @return string
	 */
	protected function _genRequestKey() {
		return hash ( 'md4', serialize ( $this->_request ) );
	}
	
	/**
	 * Generates a hash key for the exact request and quote item id
	 *
	 * @param string $itemId
	 * @param string $requestKey
	 * @return string
	 */
	protected function _genCacheKey($itemId, $requestKey) {
		return hash ( 'md4', $itemId . ':' . $requestKey );
	}
	
	/**
	 * Adds shipping cost to request as item
	 *
	 * @param Mage_Sales_Model_Quote
	 * @return int
	 */
	protected function _addShipping($quote) {
		$lineNumber = count ( $this->_lines );
		$storeId = Mage::app ()->getStore ()->getId ();
		$taxClass = Mage::helper ( 'tax' )->getShippingTaxClass ( $storeId );
		$shippingAmount = ( float ) $quote->getShippingAddress ()->getShippingAmount ();
		
		$line = new Line ( );
		$line->setNo ( $lineNumber );
		$shippingSku = 'Shipping';
		$line->setItemCode ( $shippingSku );
		$line->setDescription ( 'Shipping costs' );
		$line->setTaxCode ( $taxClass );
		$line->setQty ( 1 );
		$line->setAmount ( $shippingAmount );
		$line->setDiscounted ( false );
		
		$this->_lines [$lineNumber] = $line;
		$this->_request->setLines ( $this->_lines );
		$this->_lineToLineId [$lineNumber] = $shippingSku;
		return $lineNumber;
	}
	
	
	
	protected function _makeLog() {
		
		$log = array ();
		if ($this->_result->resultType != "SUCCESS") {
			//error log
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
		if ($this->_result->_resultEvent == "Post Invoice" || $this->_result->_resultEvent == "Pending Credit" || $this->_result->_resultEvent == "Pending Invoice") {
			//call log
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
	
	// ========================================================== //
	// Utilities, Converting Magento Objects to SpeedTax Objects
	protected function _addLineItemFromQuoteItem(Mage_Sales_Model_Quote_Item $item){
		$quote = $item->getQuote ();
		$product = $item->getProduct ();
		
		//Init new invoice line item
		$t = explode ( ' ', microtime () );
		$InvoiceNr = "INV" . $t [1];
		$this->_invoice->invoiceNumber = $InvoiceNr; //This is your invoice number for this purchase.  Leave this out for a straight query.
		$sptxPrice = new price ( );
		$sptxPrice->decimalValue = $item->getRowTotal () - $item->getDiscountAmount ();
		$sptxLineItem = new lineItem ( );
		$sptxLineItem->lineItemNumber = count( $this->_invoice->lineItems );
		
		$sptxLineItem->productCode = $this->getProductCode( $product );
		$sptxLineItem->customReference = "Reference Info";
		$sptxLineItem->quantity = $item->getQuantity ();
		$sptxLineItem->salesAmount = $sptxPrice;
		
		/**********get ship from address from config setting ***********/
		$sptxLineItem->shipFromAddress = $this->_getShippingFromAddress ();
		
		$shippingAddress = ($quote->isVirtual()) ? $quote->getShippingAddress () : $quote->getBillingAddress ();
		$ShipToAddress = $this->_getShippingToAdress ( $shippingAddress );
		$sptxLineItem->shipToAddress = $ShipToAddress;
		
		$this->_invoice->lineItems [] = $sptxLineItem;
		return true;
	}
	
	protected function _addLineItemFromOrderItem(Mage_Sales_Model_Order_Item $item){
		$quote = $item->getOrder()->getQuote(); //??
		$product = $item->getProduct (); //??
		
		//Init new invoice line item
		$t = explode ( ' ', microtime () );
		$InvoiceNr = "INV" . $t [1];
		$this->_invoice->invoiceNumber = $InvoiceNr; //This is your invoice number for this purchase.  Leave this out for a straight query.
		$sptxPrice = new price ( );
		$sptxPrice->decimalValue = $item->getRowTotal () - $item->getDiscountAmount ();
		$sptxLineItem = new lineItem ( );
		$sptxLineItem->lineItemNumber = count( $this->_invoice->lineItems );
		
		$sptxLineItem->productCode = $this->getProductCode( $product );
		$sptxLineItem->customReference = "Reference Info";
		$sptxLineItem->quantity = $item->getQuantity ();
		$sptxLineItem->salesAmount = $sptxPrice;
		
		/**********get ship from address from config setting ***********/
		$sptxLineItem->shipFromAddress = $this->_getShippingFromAddress ();
		
		$shippingAddress = ($quote->isVirtual()) ? $quote->getShippingAddress () : $quote->getBillingAddress ();
		$ShipToAddress = $this->_getShippingToAdress ( $shippingAddress );
		$sptxLineItem->shipToAddress = $ShipToAddress;
		
		$this->_invoice->lineItems [] = $sptxLineItem;
		return true;
	}

}