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

ini_set('memory_limit', '4G');
ini_set('max_input_time', 0);
require_once __DIR__ . '/../../../app/Mage.php';	  	  
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);	

$order = fopen('order.csv', 'r');
$order_address = fopen('order_address.csv', 'r');
$order_item = fopen('order_item.csv', 'r');

$offsetStart = isset($argv[1]) ? intval($argv[1]) : 0;
$limit = isset($argv[2]) ? intval($argv[2]) : 1000;

$i = 0;
$lastOrderItem = null;
while ($orderData = fgetcsv($order)) {
    $addressData = fgetcsv($order_address); // address rows map exactly to order

    if (0 == $i++) {
        $orderHeaders = $orderData;
        $orderAddressHeaders = $addressData;
        $orderItemHeaders = fgetcsv($order_item);
        continue;
    }

    foreach ($orderHeaders as $idx => $name) {
        $orderNamedData[$name] = $orderData[$idx];
    }

    foreach ($orderAddressHeaders as $idx => $name) {
        $addressNamedData[$name] = $addressData[$idx];
    }

    $objOrder = array(
        'order' => new Varien_Object($orderNamedData),
        'address' => new Varien_Object($addressNamedData),
    );

    $objOrder['items'] = array();

    // look for an order item from the last processed order
    if (!is_null($lastOrderItem)) {
        $objOrder['items'][] = new Varien_Object($lastOrderItem);
        $lastOrderItem = null;
    }

    $orderId = $orderNamedData['legacy_order_id'];
    while ($orderItemData = fgetcsv($order_item)) {
       foreach ($orderItemHeaders as $idx => $name) {
           $orderItemNamedData[$name] = $orderItemData[$idx];
       }

       if ($orderId == $orderItemNamedData['legacy_order_id']) {
           $objOrder['items'][] = new Varien_Object($orderItemNamedData);

       // it would appear this item belongs to the next order
       } else {
           $lastOrderItem = $orderItemNamedData;
           break;
       }
    }

    if ($i-1 < $offsetStart) {
       continue;
    } else if ($i-1 == $offsetStart + $limit + 1) {
       break;
    }

    try {
        placeOrder($objOrder);
    } catch (Exception $e) {
        echo "ERROR: ", $e->getMessage(), PHP_EOL;
    }

    unset($objOrder);
}

/**
 * Deprecated this code, which would read in all three order files into memory
 * and exhaust allowed memory every time.
 * - tbhuvanendran, 05/13/2012

$orderDataArray = getOrderDataArray();
$startOffset = $argv[1];
$runSize = 1000; //Performance optimization param, each process is limited to 1000 orders to avoid an expected memory leak

Mage::register('disable_order_split', true);
Mage::register('order_import_allow_ccsave', true);
Mage::register('order_import_force_product_price', true);
echo 'Processing START: ' . $startOffset . PHP_EOL;
$importCount = 0;
foreach($orderDataArray as $legacyOrderId => $orderData){
	$importCount ++;
	if($importCount < $startOffset){
		continue;
	}
	if($importCount >= $startOffset + $runSize){
		break;
	}
	try{
		if($importCount % 100 == 0){
			echo 'Processing order #' . $importCount . PHP_EOL;
		}
		placeOrder($orderData);
	}catch (Exception $e){
		if(!!$e->getMessage()){
			echo 'Error processing order ' . $legacyOrderId . ': ' . $e->getMessage() . PHP_EOL;
		}
	}
}
echo 'Import END. ' . ($startOffset + $runSize) . PHP_EOL;




// ========== FILE IO ========== //
function getOrderDataArray(){
	$orderDataArray = array();
	$orderFile = fopen("order.csv", "r");
	$row = 0;
	while (($orderRowData = fgetcsv($orderFile, 1000, ",")) !== FALSE) {
		if($row == 0){
			$orderDataHeader = $orderRowData;
		}else{
			$orderObj = new Varien_Object();
			foreach($orderDataHeader as $col => $headerName){
				$orderObj->setData(trim($headerName), trim($orderRowData[$col]));
			}
			$orderDataArray[$orderObj->getData('legacy_order_id')]['order'] = $orderObj;
		}
		$row ++;
	}
	fclose($orderFile);
	
	$orderAddressFile = fopen("order_address.csv", "r");
	$row = 0;
	while (($orderAddressRowData = fgetcsv($orderAddressFile, 1000, ",")) !== FALSE) {
		if($row == 0){
			$orderAddressDataHeader = $orderAddressRowData;
		}else{
			$orderAddressObj = new Varien_Object();
			foreach($orderAddressDataHeader as $col => $headerName){
				$orderAddressObj->setData(trim($headerName), trim($orderAddressRowData[$col]));
			}
			$orderDataArray[$orderAddressObj->getData('legacy_order_id')]['address'] = $orderAddressObj;
		}
		$row ++;
	}
	fclose($orderAddressFile);
	
	$orderItemFile = fopen("order_item.csv", "r");
	$row = 0;
	while (($orderItemRowData = fgetcsv($orderItemFile, 1000, ",")) !== FALSE) {
		if($row == 0){
			$orderItemDataHeader = $orderItemRowData;
		}else{
			$orderItemObj = new Varien_Object();
			foreach($orderItemDataHeader as $col => $headerName){
				$orderItemObj->setData(trim($headerName), trim($orderItemRowData[$col]));
			}
			$orderDataArray[$orderItemObj->getData('legacy_order_id')]['items'][] = $orderItemObj;
		}
		$row ++;
	}
	fclose($orderItemFile);
	return $orderDataArray;
}
*/


// ========== ORDER PLACEMENT ========== //
function placeOrder($orderData){
	$orderObj = $orderData['order'];
	$order = Mage::getModel('sales/order')->loadByIncrementId($orderObj->getData('legacy_order_id'));
	if(!!$order && !!$order->getId()){
		throw new Exception('Order already exists! ' . $orderObj->getData('legacy_order_id'));
	}
	
	$quote = Mage::getModel('sales/quote');
	
	// ==============================
	//Set customer
	$customerEmail = _trimGmail($orderObj->getData('customer_email'));
	$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($customerEmail);
	if(!$customer || !$customer->getId()){
		throw new Exception('Order ' . $orderObj->getData('legacy_order_id') . ': Invalid customer email "' . $customerEmail . '"');
	}

	// ==============================
	//Add products
	foreach($orderData['items'] as $orderItemObj){
		$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $orderItemObj->getData('sku'));
		if(!$product || !$product->getId()){
			throw new Exception('Order ' . $orderObj->getData('legacy_order_id') . ':Invalid product SKU "' . $orderItemObj->getData('sku') . '"');
		}
		$qty = $orderItemObj->getData('qty_ordered') - $orderItemObj->getData('qty_canceled');
		if ($qty > 0 || $orderObj->getData('cancel') == '1') {
			$item = $quote->addProduct($product, $qty);
			$item->getProduct()->setOrderImportFinalPrice($orderItemObj->getData('price'));
		}
	}

	// ==============================
	//Set billing and shipping addresses
	$quote->assignCustomer($customer);
	$orderAddressObj = $orderData['address'];
		
	//Billing and shipping address complement each other if some info is missing
	$orderObj->setData('firstname', $orderObj->getData('firstname')?$orderObj->getData('firstname'):$orderAddressObj->getData('firstname'));
	$orderObj->setData('lastname', $orderObj->getData('lastname')?$orderObj->getData('lastname'):$orderAddressObj->getData('lastname'));
	$orderObj->setData('street', $orderObj->getData('street')?$orderObj->getData('street'):$orderAddressObj->getData('street'));
	$orderObj->setData('city', $orderObj->getData('city')?$orderObj->getData('city'):$orderAddressObj->getData('city'));
	$orderObj->setData('postcode', $orderObj->getData('postcode')?$orderObj->getData('postcode'):$orderAddressObj->getData('postcode'));
	$orderObj->setData('region', $orderObj->getData('region')?$orderObj->getData('region'):$orderAddressObj->getData('region'));
	$orderObj->setData('country_id', $orderObj->getData('country_id')?$orderObj->getData('country_id'):$orderAddressObj->getData('country_id'));
	$orderObj->setData('telephone', $orderObj->getData('telephone')?$orderObj->getData('telephone'):$orderAddressObj->getData('telephone'));

	$orderAddressObj->setData('firstname', $orderAddressObj->getData('firstname')?$orderAddressObj->getData('firstname'):$orderObj->getData('firstname'));
	$orderAddressObj->setData('lastname', $orderAddressObj->getData('lastname')?$orderAddressObj->getData('lastname'):$orderObj->getData('lastname'));
	$orderAddressObj->setData('street', $orderAddressObj->getData('street')?$orderAddressObj->getData('street'):$orderObj->getData('street'));
	$orderAddressObj->setData('city', $orderAddressObj->getData('city')?$orderAddressObj->getData('city'):$orderObj->getData('city'));
	$orderAddressObj->setData('postcode', $orderAddressObj->getData('postcode')?$orderAddressObj->getData('postcode'):$orderObj->getData('postcode'));
	$orderAddressObj->setData('region', $orderAddressObj->getData('region')?$orderAddressObj->getData('region'):$orderObj->getData('region'));
	$orderAddressObj->setData('country_id', $orderAddressObj->getData('country_id')?$orderAddressObj->getData('country_id'):$orderObj->getData('country_id'));
	$orderAddressObj->setData('telephone', $orderAddressObj->getData('telephone')?$orderAddressObj->getData('telephone'):$orderObj->getData('telephone'));

	//Import data some phone number might be missing
	if(!$orderObj->getData('telephone')){
		$orderObj->setData('telephone', '0');
	}
	if(!$orderAddressObj->getData('telephone')){
		$orderAddressObj->setData('telephone', '0');
	}
	
	$customerAddress = Mage::getModel('customer/address');
	$customerAddress->setData('firstname', $orderObj->getData('firstname'));
	$customerAddress->setData('lastname', $orderObj->getData('lastname'));
	$customerAddress->setData('street', $orderObj->getData('street'));
	$customerAddress->setData('city', $orderObj->getData('city'));
	$customerAddress->setData('postcode', $orderObj->getData('postcode'));
	$customerAddress->setData('region', $orderObj->getData('region'));
	$customerAddress->setData('country_id', $orderObj->getData('country_id'));
	$customerAddress->setData('telephone', $orderObj->getData('telephone'));
	$quote->getBillingAddress()->importCustomerAddress($customerAddress)->implodeStreetAddress();
	
	if(!$quote->isVirtual()){
		$customerAddress = Mage::getModel('customer/address');
		$customerAddress->setData('firstname', $orderAddressObj->getData('firstname'));
		$customerAddress->setData('lastname', $orderAddressObj->getData('lastname'));
		$customerAddress->setData('street', $orderAddressObj->getData('street'));
		$customerAddress->setData('city', $orderAddressObj->getData('city'));
		$customerAddress->setData('postcode', $orderAddressObj->getData('postcode'));
		$customerAddress->setData('region', $orderAddressObj->getData('region'));
		$customerAddress->setData('country_id', $orderAddressObj->getData('country_id'));
		$customerAddress->setData('telephone', $orderAddressObj->getData('telephone'));
		$quote->getShippingAddress()->importCustomerAddress($customerAddress)->implodeStreetAddress();
	}
	
	// ==============================
	//Shipping method/rate
	if(!$quote->isVirtual()){
		$quote->getShippingAddress()->setCollectShippingRates(array('rates'));
		$quote->getShippingAddress()->setShippingMethod('flexible_flexible');
		Mage::unregister('order_import_shipping_amount');
		//Shipping = base + oversize shipping
		Mage::register('order_import_shipping_amount', 
				$orderObj->getData('base_shipping_amount') + $orderObj->getData('shipping_amount')
		);
		$quote->getShippingAddress()->setPaymentMethod($data['method']);
	}
	
	// ==============================
	//Tax rate
	Mage::unregister('order_import_tax_amount');
	Mage::register('order_import_tax_amount', $orderObj->getData('tax_amount'));
	
	// ==============================
	//Discount
	$discount = -1.0 * ($orderObj->getData('discount_amount') + $orderObj->getData('reward_currency_amount'));
	Mage::unregister('order_import_discount_amount');
	Mage::register('order_import_discount_amount', $discount);
	
	$ccType = $orderObj->getData('cc_type');
	switch($orderObj->getData('cc_type')){
		case 'VISA':
			$ccType = 'VI';
			break;
		case 'AMEX':
			$ccType = 'AE';
			break;
	}
	$data = array(
			'method'=>'ccsave',
			'cc_type' => $ccType,
			'cc_owner' => $orderObj->getData('cc_owner')?$orderObj->getData('cc_owner'):$customer->getData('firstname') . ' ' . $customer->getData('lastname'),
			'cc_number' => $orderObj->getData('cc_last4'),
	);	
	$payment = $quote->getPayment();
	$payment->importData($data); //Automatic total collection
	
	// ==============================
	//Final grand total check, some rules like $10 xth-order, or $15 yth-order are not captured in the 'discount_amount' field, need to force that the grand total is correct
	if($quote->isVirtual()){
		$totalAddress = $quote->getBillingAddress();
	}else{
		$totalAddress = $quote->getShippingAddress();
	}
	
	$grandTotal = $totalAddress->getData('grand_total');
	$delta = $orderObj->getData('grand_total') - $grandTotal;
	//Based on Magento calculation accurracy
	if(abs($delta) > 0.00001){
		//Force into discount
		$discount += $delta;
		Mage::unregister('order_import_discount_amount');
		Mage::register('order_import_discount_amount', $discount);
		$quote->setData('totals_collected_flag', false)->collectTotals();
	}

	// ==============================
	//Placing order
        $quote->setStoreId($orderObj->getData('store_id'));
	$service = Mage::getModel('sales/service_quote', $quote);
	$service->submitAll();
	$order = $service->getOrder();
	
	// ==============================
	//Post processing, 'created_at': with timezone correction, 'legacy_order_id': as increment ID
	$defaultTimezone = date_default_timezone_get();
	$mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
	date_default_timezone_set($mageTimezone);
	$createdAt = strtotime($orderObj->getData('created_at'));
	date_default_timezone_set($defaultTimezone);

	if ($orderObj->getData('cancel') == '1') {
		$order->setStatus('canceled');
	} else {
		$order->setStatus('complete');
	}

	$order->setIncrementId($orderObj->getData('legacy_order_id'))
		->setCreatedAt($createdAt)
		->save();
}

// ========== Special logic for gmail accounts ========== //
function _trimGmail($email) {
    $strArray = explode('@', $email);
    if(empty($strArray) ||
       empty($strArray[1]) ||
       $strArray[1] != 'gmail.com') {
            return $email;
    }
    //get username, such as 'abcd'
    $username = $strArray[0];
    //Get username string's length
    $len = strlen($username);
    $trimmedGmail = '';
    
    //iterate chacrates in username string
    for($j=0; $j<$len; $j++) {
		//if encounters '+', discard the rest of the string
		if($username[$j] == '+') {
			break;
		}
		//check if it is '.', if yes, don't concatenate.
		if($username[$j] != '.') {
			//concatenate username chacrater
			$trimmedGmail .= $username[$j];
		}
    }

    $trimmedGmail .= '@gmail.com';
    return $trimmedGmail;
}
