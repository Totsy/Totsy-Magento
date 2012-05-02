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

ini_set('memory_limit', '3G');
ini_set('max_input_time', 0);
require_once '../app/Mage.php';	  	  
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);	

$orderDataArray = getOrderDataArray();

Mage::register('disable_order_split', true);
Mage::register('order_import_allow_ccsave', true);
echo 'Processing START: ' . PHP_EOL;
$importCount = 1;
foreach($orderDataArray as $legacyOrderId => $orderData){
	try{
		if($importCount % 10 == 0){
			echo 'Processing order #' . $importCount . PHP_EOL;
		}
		placeOrder($orderData);
	}catch (Exception $e){
		echo 'Error processing order ' . $legacyOrderId . ': ' . $e->getMessage() . PHP_EOL;
	}
	$importCount ++;
}
echo 'Import END.' . PHP_EOL;




// ========== FILE IO ========== //
function getOrderDataArray(){
	$orderDataArray = array();
	$orderFile = fopen("orderimport/order.csv", "r");
	$row = 0;
	while (($orderRowData = fgetcsv($orderFile, 1000, ",")) !== FALSE) {
		if($row == 0){
			$orderDataHeader = $orderRowData;
		}else{
			$orderObj = new Varien_Object();
			foreach($orderDataHeader as $col => $headerName){
				$orderObj->setData($headerName, $orderRowData[$col]);
			}
			$orderDataArray[$orderObj->getData('legacy_order_id')]['order'] = $orderObj;
		}
		$row ++;
	}
	fclose($orderFile);
	
	$orderAddressFile = fopen("orderimport/order_address.csv", "r");
	$row = 0;
	while (($orderAddressRowData = fgetcsv($orderAddressFile, 1000, ",")) !== FALSE) {
		if($row == 0){
			$orderAddressDataHeader = $orderAddressRowData;
		}else{
			$orderAddressObj = new Varien_Object();
			foreach($orderAddressDataHeader as $col => $headerName){
				$orderAddressObj->setData($headerName, $orderAddressRowData[$col]);
			}
			$orderDataArray[$orderAddressObj->getData('legacy_order_id')]['address'] = $orderAddressObj;
		}
		$row ++;
	}
	fclose($orderAddressFile);
	
	$orderItemFile = fopen("orderimport/order_item.csv", "r");
	$row = 0;
	while (($orderItemRowData = fgetcsv($orderItemFile, 1000, ",")) !== FALSE) {
		if($row == 0){
			$orderItemDataHeader = $orderItemRowData;
		}else{
			$orderItemObj = new Varien_Object();
			foreach($orderItemDataHeader as $col => $headerName){
				$orderItemObj->setData($headerName, $orderItemRowData[$col]);
			}
			$orderDataArray[$orderItemObj->getData('legacy_order_id')]['items'][] = $orderItemObj;
		}
		$row ++;
	}
	fclose($orderItemFile);
	return $orderDataArray;
}




// ========== ORDER PLACEMENT ========== //
function placeOrder($orderData){
	$quote = Mage::getModel('sales/quote');
	
	$orderObj = $orderData['order'];
	$customerEmail = $orderObj->getData('customer_email');
	$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($customerEmail);
	if(!$customer || !$customer->getId()){
		throw new Exception('Invalid customer! Email: ' . $customerEmail);
	}
	$quote->assignCustomer($customer);
	
	$customerAddress = Mage::getModel('customer/address');
	$orderAddressObj = $orderData['address'];
	$customerAddress->setData('firstname', $orderAddressObj->getData('firstname'));
	$customerAddress->setData('lastname', $orderAddressObj->getData('lastname'));
	$customerAddress->setData('street', $orderAddressObj->getData('street'));
	$customerAddress->setData('city', $orderAddressObj->getData('city'));
	$customerAddress->setData('postcode', $orderAddressObj->getData('postcode'));
	$customerAddress->setData('region', $orderAddressObj->getData('region'));
	$customerAddress->setData('country_id', $orderAddressObj->getData('country_id'));
	$customerAddress->setData('telephone', $orderAddressObj->getData('telephone'));
	$quote->getBillingAddress()->importCustomerAddress($customerAddress)->implodeStreetAddress();
	$quote->getShippingAddress()->importCustomerAddress($customerAddress)->implodeStreetAddress();
	
	foreach($orderData['items'] as $orderItemObj){
		$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $orderItemObj->getData('sku'));
		if(!$product || !$product->getId()){
			throw new Exception('Invalid product! SKU: ' . $orderItemObj->getData('sku'));
		}
		$quote->addProduct($product, $orderItemObj->getData('qty_ordered'));
	}
	
	$quote->getShippingAddress()->setCollectShippingRates(array('rates'));
	$quote->getShippingAddress()->setShippingMethod('flexible_flexible');
	Mage::unregister('split_order_force_free_shipping');
	if($orderObj->getData('shipping_amount') == 0){
		Mage::register('split_order_force_free_shipping', true);
	}
	$quote->getShippingAddress()->setPaymentMethod($data['method']);
	
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
			'cc_owner' => $orderObj->getData('cc_owner'),
			'cc_number' => $orderObj->getData('cc_last4'),
	);	
	$payment = $quote->getPayment();
	$payment->importData($data); //Automatic total collection
	       
	$service = Mage::getModel('sales/service_quote', $quote);
	$service->submitAll();
}