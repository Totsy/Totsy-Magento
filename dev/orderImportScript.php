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
Mage::register('order_import_force_product_price', true);
echo 'Processing START: ' . PHP_EOL.'<br/>';
$importCount = 1;
foreach($orderDataArray as $legacyOrderId => $orderData){
	try{
		if($importCount % 20 == 0){
			echo 'Processing order #' . $importCount . PHP_EOL.'<br/>';
		}
		placeOrder($orderData);
	}catch (Exception $e){
		echo 'Error processing order ' . $legacyOrderId . ': ' . $e->getMessage() . PHP_EOL.'<br/>';
	}
	$importCount ++;
}
echo 'Import END.' . PHP_EOL.'<br/>';




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
				$orderObj->setData(trim($headerName), trim($orderRowData[$col]));
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
				$orderAddressObj->setData(trim($headerName), trim($orderAddressRowData[$col]));
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
				$orderItemObj->setData(trim($headerName), trim($orderItemRowData[$col]));
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
	$orderObj = $orderData['order'];
	$order = Mage::getModel('sales/order')->loadByIncrementId($orderObj->getData('legacy_order_id'));
	if(!!$order && !!$order->getId()){
		throw new Exception('Order already exists! ' . $orderObj->getData('legacy_order_id'));
	}
	
	$quote = Mage::getModel('sales/quote');
	
	//Set customer
	$customerEmail = $orderObj->getData('customer_email');
	$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($customerEmail);
	if(!$customer || !$customer->getId()){
		throw new Exception('Invalid customer! Email: ' . $customerEmail);
	}
	
	//Add products
	foreach($orderData['items'] as $orderItemObj){
		$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $orderItemObj->getData('sku'));
		if(!$product || !$product->getId()){
			throw new Exception('Invalid product! SKU: ' . $orderItemObj->getData('sku'));
		}
		$qty = $orderItemObj->getData('qty_ordered') - $orderItemObj->getData('qty_canceled');
		if($qty > 0){
			$item = $quote->addProduct($product, $qty);
			$item->getProduct()->setOrderImportFinalPrice($orderItemObj->getData('price'));
		}
	}
	
	//Set billing and shipping addresses
	$quote->assignCustomer($customer);
	$orderAddressObj = $orderData['address'];
	
	$customerAddress = Mage::getModel('customer/address');
	$customerAddress->setData('firstname', $orderObj->getData('firstname')?$orderObj->getData('firstname'):$orderAddressObj->getData('firstname'));
	$customerAddress->setData('lastname', $orderObj->getData('lastname')?$orderObj->getData('lastname'):$orderAddressObj->getData('lastname'));
	$customerAddress->setData('street', $orderObj->getData('street')?$orderObj->getData('street'):$orderAddressObj->getData('street'));
	$customerAddress->setData('city', $orderObj->getData('city')?$orderObj->getData('city'):$orderAddressObj->getData('city'));
	$customerAddress->setData('postcode', $orderObj->getData('postcode')?$orderObj->getData('postcode'):$orderAddressObj->getData('postcode'));
	$customerAddress->setData('region', $orderObj->getData('region')?$orderObj->getData('region'):$orderAddressObj->getData('region'));
	$customerAddress->setData('country_id', $orderObj->getData('country_id')?$orderObj->getData('country_id'):$orderAddressObj->getData('country_id'));
	$customerAddress->setData('telephone', $orderObj->getData('telephone')?$orderObj->getData('telephone'):$orderAddressObj->getData('telephone'));
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
	
	//Shipping rate
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
	
	//Tax rate
	Mage::unregister('order_import_tax_amount');
	Mage::register('order_import_tax_amount', $orderObj->getData('tax_amount'));
	
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
	
	//Final grand total check
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
	
	$service = Mage::getModel('sales/service_quote', $quote);
	$service->submitAll();
	$order = $service->getOrder();
	$order->setIncrementId($orderObj->getData('legacy_order_id'))
			->setCreatedAt(strtotime($orderObj->getData('created_at')))
			->save();
}