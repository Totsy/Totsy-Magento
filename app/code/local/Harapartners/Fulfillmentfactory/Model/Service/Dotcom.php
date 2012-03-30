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
class Harapartners_Fulfillmentfactory_Model_Service_Dotcom
{
	const ONE_DAY_SECONDS = 86400;
	
	/**
	 * get yesterday's date string
	 *
	 * @return date
	 */
	protected function _getYesterday() {
		return date("Y-m-d 00:00:00", mktime(0, 0, 0, date("m"),date("d")-1,date("Y")));	//UTC
	}
	
	//===============For Cronjob===============//
	
	/**
	 * schedule to run product update
	 *
	 */
	public function runUpdateProduct() {
		//put one day ago as default
		$fromDate = $this->_getYesterday();
		$this->submitProductsToDotcomByDate($fromDate);
	}
	
	/**
	 * schedule to submit purchase orders
	 *
	 */
	public function runSubmitPurchaseOrders() {
		//put one day ago as default
		$fromDate = $this->_getYesterday();
		$toDate = date("Y-m-d 23:00:00");
		
		$this->submitPurchaseOrdersToDotcomByDate($fromDate, $toDate);
	}
	
	/**
	 * schedule to run order fulfillment
	 *
	 */
	public function runDotcomFulfillOrder() {
		//fetch inventory data from DOTcom
		$inventoryList = $this->updateInventory();
		//update stock info
		$service = Mage::getModel('fulfillmentfactory/service_fulfillment');
		$processingOrderCollection = $service->stockUpdate($inventoryList);
		//update order's info
		$service->updateOrderFulfillStatus($processingOrderCollection);
		//submit orders to fulfill
		$this->submitOrderToFulfillByQueue();
	}
	
	/**
	 * schedule to update shimpent
	 *
	 */
	public function runUpdateShipment() {
		//put one day as default
		$fromDate = $this->_getYesterday();
		$toDate = date("Y-m-d 00:00:00");
		
		$this->updateShipment($fromDate, $toDate);
	}
	
	//===============Functions===============//
	
	/**
	 * post products information to Dotcom, by created date
	 *
	 * @param string $createdAfter all products' created date should after this date
	 */
	public function submitProductsToDotcomByDate($createdAfter = '2012-01-01 00:00:00') {
		$products = Mage::getModel('catalog/product')->getCollection()
													 ->addAttributeToSelect('*')
													 ->addAttributeToFilter('created_at', array('from' => $createdAfter));	
  		
  										
  		return $this->submitProductsToDotcom($products);
	}
	
	/**
	 * post products information to Dotcom
	 *
	 * @param array $products for products we want to submit
	 * @return response
	 */
	public function submitProductsToDotcom($products) {
		$xml = '<items xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
		
		foreach($products as $product) {
			//only process simple product
			if($product->getTypeId() != 'simple') {
				continue;
			}
			
			$xml .= <<<XML
				<item>
					<sku><![CDATA[{$product->getSku()}]]></sku>
					<description><![CDATA[{$product->getName()}]]></description>
					<upc xsi:nil="true" />
					<weight xsi:nil="true" />
					<cost xsi:nil="true"/>
					<price>{$product->getPrice()}</price>
					<root-sku xsi:nil="true"/>
					<package-qty xsi:nil="true"/>
					<serial-indicator xsi:nil="true"/>
					<client-company xsi:nil="true"/>
					<client-department xsi:nil="true"/>
					<client-product-class xsi:nil="true"/>
					<client-product-type xsi:nil="true"/>
					<avg-cost xsi:nil="true"/>
					<master-pack xsi:nil="true"/>
					<item-barcode xsi:nil="true"/>
					<country-of-origin xsi:nil="true"/>
					<harmonized-code xsi:nil="true"/>
					<manufacturing-code xsi:nil="true"/>
					<style-number xsi:nil="true"/>
					<short-name xsi:nil="true"/>
					<color xsi:nil="true"/>
					<size xsi:nil="true"/>
					<long-description xsi:nil="true" />
				</item>
XML;
		}
		
		$xml .= '</items>';
		
		$response = Mage::helper('fulfillmentfactory/dotcom')->submitProductItems($xml);
		
		return $response;
	}
	
	/**
	 * post purchase orders to Dotcom, by desired quantity
	 *
	 * @param int $quantity
	 */
	public function submitPurchaseOrdersToDotcomByDate($fromDate = '', $toDate = '') {
		$orders = Mage::getModel('sales/order')->getCollection()
											->addAttributeToFilter('state', Mage_Sales_Model_Order::STATE_NEW)
											->addAttributeToFilter('created_at', array(
																					'from' => $fromDate,
																					'to' => $toDate
																				)
											);
		
		$itemsArray = array();
		
		foreach($orders as $order) {
			$items = $order->getAllItems();
			
			foreach($items as $item) {
				$sku = $item->getSku();
				$qty = $item->getQtyOrdered();
				
				if(isset($itemsArray[$sku])) {
					$itemsArray[$sku] += $qty;
				}
				else {
					$itemsArray[$sku] = $qty;
				}
			}
		}
		
		//echo print_r($itemsArray, 1);
	
		return $this->submitPurchaseOrdersToDotcom($itemsArray);
	}
	
	/**
	 * post purchase orders to Dotcom
	 *
	 * @param array $orders for orders we want to submit
	 * @return response
	 */
	public function submitPurchaseOrdersToDotcom($items) {
		$poNumber = 'po_' . date('YmdHsi');
		
		$xml = '<purchase_orders xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
		
		$xml .= <<<XML
			<purchase_order>
				<po-number><![CDATA[$poNumber]]></po-number>
				<priority-date xsi:nil="true" />
				<expected-on-dock xsi:nil="true" />
				<items>
XML;

		foreach($items as $sku => $qty) {
			$productSku = $sku;
			$quantity = $qty;
			$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSku);
			
			if(empty($product) || !$product->getId()) {
				continue;
			}
			
			$xml .= <<<XML
					<item>
						<sku><![CDATA[{$product->getSku()}]]></sku>
						<description><![CDATA[{$product->getName()}]]></description>
						<quantity>$quantity</quantity>
						<upc xsi:nil="true" />
						<weight xsi:nil="true" />
						<cost xsi:nil="true" />
						<price>{$product->getPrice()}</price>
						<root-sku xsi:nil="true" />
						<package-qty xsi:nil="true" />
						<serial-indicator xsi:nil="true" />
						<client-company xsi:nil="true" />
						<client-department xsi:nil="true" />
						<client-product-class xsi:nil="true" />
						<client-product-type xsi:nil="true" />
						<avg-cost xsi:nil="true" />
						<master-pack xsi:nil="true" />
						<item-barcode xsi:nil="true" />
						<country-of-origin xsi:nil="true" />
						<harmonized-code xsi:nil="true" />
						<manufacturing-code xsi:nil="true" />
						<style-number xsi:nil="true" />
						<short-name xsi:nil="true" />
						<color xsi:nil="true" />
						<size xsi:nil="true" />
						<long-description xsi:nil="true" />
					</item>
XML;
		}
						
		$xml .=	<<<XML
				</items>
			</purchase_order>
		'</purchase_orders>'
XML;
		
		//echo $xml;
		
		$response = Mage::helper('fulfillmentfactory/dotcom')->submitPurchaseOrders($xml);
		
		return $response;
	}
	
	/**
	 * get inventory from Dotcom and run stock update
	 *
	 */
	public function updateInventory() {
		//get data from dotcom
		$dataXML = Mage::helper('fulfillmentfactory/dotcom')->getInventory();
		
		if(!empty($dataXML)) {
			$inventoryList = array();
			
			foreach($dataXML as $item) {
				$inventory = array();
				
				$qty = (int)$item->quantity_available;
				
				if($qty > 0) {
					$inventory['sku'] = (string)$item->sku;
					$inventory['qty'] = $qty;
					
					$inventoryList[] = $inventory;
				}
			}
			
			return $inventoryList;
		}
	}
	
	/**
	 * submit orders which are ready
	 *
	 * @return response
	 */
	public function submitOrderToFulfillByQueue() {
		$itemQueueCollection = Mage::getModel('fulfillmentfactory/itemqueue')->getCollection()->loadReadyForSubmitItemQueue();
		
		$orderArray = array();
		
		foreach($itemQueueCollection as $itemqueue) {
			$order = Mage::getModel('sales/order')->load($itemqueue->getOrderId());
			Mage::helper('fulfillmentfactory')->_pushUniqueOrderIntoArray($orderArray, $order);
			
			//change status
			$itemqueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SUBMITTED)
						  ->save();
		}
		
		return $this->submitOrdersToFulfill($orderArray);
	}
	
	/**
	 * submit orders to Dotcom for fulfillment, by quantity
	 *
	 * @param array $orders for orders we want to submit
	 * @return response
	 */
	public function submitOrdersToFulfill($orders) {												   
		$xml = '<orders xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
		
		foreach($orders as $order) {
			
			try {
				//capture payment
				$orderPayment = $order->getPayment();
				$orderPayment->getMethodInstance()->setData('forced_payment_action', 
																Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE);
				$orderPayment->getMethodInstance()->setData('cybersource_subid', $orderPayment->getCybersourceSubid());
				$orderPayment->place();
				
				//update order information
				$order->setStatus('processing');
				$transactionSave = Mage::getModel('core/resource_transaction')
	                    ->addObject($order);
	                    
	           	$transactionSave->save();
			}
			catch(Exception $e) {
				throw new Exception('Order ' . $order->getIncrementId() . ' could not place the payment. ' . $e->getMessage());
				continue;
			}
			
			$orderDate = date("Y-m-d", strtotime($order->getCreatedAt()));
			$shippingMethod = Mage::helper('fulfillmentfactory/dotcom')->getDotcomShippingMethod($order->getShippingMethod());
			$shippingAddress = $order->getShippingAddress();
			
			//to avoid null object
			if(empty($shippingAddress)) {
				$shippingAddress = Mage::getModel('sales/order_address');
			}
			
			$shippingName = $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname();
			
			$state = Mage::helper('fulfillmentfactory')->getStateCodeByFullName($shippingAddress->getRegion(), $shippingAddress->getCountry());
			
			$xml .= <<<XML
			<order>
				<order-number>{$order->getIncrementId()}</order-number>
				<order-date>{$orderDate}</order-date>
				<ship-method>{$shippingMethod}</ship-method>
				<ship_via xsi:nil="true"/>
				<special-instructions xsi:nil="true"/>
				<special-messaging xsi:nil="true"/>
				<drop-ship xsi:nil="true"/>
				<invoice-number xsi:nil="true"/>
				<ok-partial-ship xsi:nil="true"/>
				<declared-value xsi:nil="true"/>
				<cancel-date xsi:nil="true"/>
				<total-tax>{$order->getTaxAmount()}</total-tax>
				<total-shipping-handling>{$order->getShippingAmount()}</total-shipping-handling>
				<total-discount xsi:nil="true"/>
				<total-order-amount>{$order->getGrandTotal()}</total-order-amount>
				<po-number xsi:nil="true"/>
				<salesman xsi:nil="true"/>
				<credit-card-number xsi:nil="true"/>
				<credit-card-expiration xsi:nil="true"/>
				<ad-code></ad-code>
				<continuity-flag xsi:nil="true"/>
				<freight-terms xsi:nil="true"/>
				<department>01</department>
				<pay-terms xsi:nil="true"/>
				<tax-percent xsi:nil="true"/>
				<asn-qualifier xsi:nil="true"/>
				<gift-order-indicator xsi:nil="true"/>
				<order-source xsi:nil="true"/>
				<ship_date xsi:nil="true"/>
				<promise-date xsi:nil="true"/>
				<third-party-account xsi:nil="true"/>
				<priority xsi:nil="true"/>
				<retail-department xsi:nil="true"/>
				<retail-store xsi:nil="true"/>
				<retail-vendor xsi:nil="true"/>
				<pool xsi:nil="true"/>
				<billing-information>
					<billing-customer-number xsi:nil="true"/>
					<billing-name><![CDATA[{$shippingName}]]></billing-name>
					<billing-company xsi:nil="true"/>
					<billing-address1><![CDATA[{$shippingAddress->getStreet(1)}]]></billing-address1>
					<billing-address2><![CDATA[{$shippingAddress->getStreet(2)}]]></billing-address2>
					<billing-address3 xsi:nil="true"/>
					<billing-city><![CDATA[{$shippingAddress->getCity()}]]></billing-city>
					<billing-state>{$state}</billing-state>
					<billing-zip>{$shippingAddress->getPostcode()}</billing-zip>
					<billing-country xsi:nil="true"/>
					<billing-phone xsi:nil="true"/>
					<billing-email xsi:nil="true"/>
				</billing-information>
				<shipping-information>
					<shipping-customer-number xsi:nil="true"/>
					<shipping-name xsi:nil="true"/>
					<shipping-company xsi:nil="true"/>
					<shipping-address1 xsi:nil="true"/>
					<shipping-address2 xsi:nil="true"/>
					<shipping-address3 xsi:nil="true"/>
					<shipping-city xsi:nil="true"/>
					<shipping-state xsi:nil="true"/>
					<shipping-zip xsi:nil="true"/>
					<shipping-country xsi:nil="true"/>
					<shipping-iso-country-code xsi:nil="true"/>
					<shipping-phone xsi:nil="true"/>
					<shipping-email xsi:nil="true"/>
				</shipping-information>
				<store-information>
					<store-name xsi:nil="true"/>
					<store-address1 xsi:nil="true"/>
					<store-address2 xsi:nil="true"/>
					<store-city xsi:nil="true"/>
					<store-state xsi:nil="true"/>
					<store-zip xsi:nil="true"/>
					<store-country xsi:nil="true"/>
					<store-phone xsi:nil="true"/>
				</store-information>
				<custom-fields>
				<custom-field-1 xsi:nil="true"/>
				<custom-field-2 xsi:nil="true"/>
				<custom-field-3 xsi:nil="true"/>
				<custom-field-4 xsi:nil="true"/>
				<custom-field-5 xsi:nil="true"/>
				</custom-fields>
				<line-items>
XML;
			$items = $order->getAllItems();

			foreach($items as $item) {
				$quantity = intval($item->getQtyOrdered());
				
				$xml .= <<<XML
					<line-item>
						<sku>{$item->getSku()}</sku>
						<quantity>{$quantity}</quantity>
						<price>{$item->getPrice()}</price>
						<tax>{$item->getTaxAmount()}</tax>
						<shipping-handling>0</shipping-handling>
						<client-item xsi:nil="true"/>
						<line-number xsi:nil="true"/>
						<gift-box-wrap-quantity xsi:nil="true"/>
						<gift-box-wrap-type xsi:nil="true"/>
					</line-item>
XML;
			}

			$xml .= <<<XML
				</line-items>
			</order>
XML;
		}
		
		$xml .= '</orders>';
		
		//echo $xml;
		
		$response = Mage::helper('fulfillmentfactory/dotcom')->submitOrders($xml);
		
		return $response;
	}
	
	/**
	 * update shipment information to Magento database
	 *
	 * @param string $fromDate	e.g. 2010-01-01 00:00:00
	 * @param string $toDate	e.g. 2012-11-11 00:00:00
	 */
	public function updateShipment($fromDate = '', $toDate = '') {
		
		if(empty($fromDate)) {
			$fromDate = $this->_getYesterday();	//put 1 day before
		}
		
		if(empty($toDate)) {
			$toDate = date('Y-m-d H:i:s', (strtotime($fromDate) + self::ONE_DAY_SECONDS));	//put 1 day after from date
		}
		
		//get data from dotcom
		$dataXML = Mage::helper('fulfillmentfactory/dotcom')->getShipment($fromDate, $toDate);
		
		foreach($dataXML as $shipment) {
			$attr = $shipment->attributes('i', TRUE);
			
			if(!$attr['nil']) {
				$orderId = (string)$shipment->client_order_number;
				
				$shipmentXmlItems = $shipment->ship_items->children('a', TRUE);
				foreach($shipmentXmlItems as $shipmentXmlItem) {
					$trackingNumber = (string)$shipmentXmlItem->tracking_number;
					
					if(empty($trackingNumber)) {
						continue;
					}
					
					//check if tracking number exists
					$queryTrackingResult = Mage::getModel('sales/order_shipment_track')->getCollection()
												->addFieldToFilter('track_number', $trackingNumber);
												
					if(empty($queryTrackingResult) || (count($queryTrackingResult) <= 0)) {
						$title = (string)$shipmentXmlItem->carrier;
						$carrier = (string)$shipmentXmlItem->carrier;	//TODO mapping carrier to Magento carrier code
						$trackingData = array (
							'carrier_code'=>$carrier,
							'title'=>$title,
							'number'=>$trackingNumber
						);
						
						$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
						
						if(!!$order && !!$order->getId()) {
							$shipmentCollection = $order->getShipmentsCollection();
							$shipment = Mage::getModel('sales/order_shipment');
							
							if(!empty($shipmentCollection) && (count($shipmentCollection) > 0)) {
								$shipment = $shipmentCollection->getFirstItem();	//get first item
							}
							else {
								$itemQtyArray = array();
								foreach ($order->getAllItems() as $item){
									$itemQtyArray[$item->getData('item_id')] = (int)$item->getData('qty_ordered'); 
								}
								
								$shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($itemQtyArray);
							}
							
							$track = Mage::getModel('sales/order_shipment_track')->addData($trackingData);
							$shipment->addTrack($track);

							$shipment->sendEmail(true);
							$shipment->setEmailSent(true);
							
							$shipment->save();
							$order->setStatus('complete')
								  ->save();
						}
					}
				}
			}
		}
	}
	
	
	//===============Test Function===============//
	
	public function testSubmitProductsToDotcom() {
		$products = Mage::getModel('catalog/product')->getCollection()
													->addAttributeToSelect('*')
													->addAttributeToFilter('entity_id', array('gt' => '1075'));
  		
  										
  		return $this->submitProductsToDotcom($products);
	}
	
	public function testSubmitPurchaseOrdersToDotcom() {
		/*$testItems = array(
			'2PK-NSS-911' => 96,
			'BL-HT-POM' => 10,
			'2PK-KN-911' => 100,
			'STB-TT' => 50,
			'MKY-FRM' => 20,
			'OWL-FRD' => 50,
			'LKS-123' => 50,
			'FRY-DST' => 50,
			'LRG-PRIN' => 50,
			'ABA-CUS' => 50,
			'JET-PLN' => 50,
			'12-Your-Sku' => 50,
			'TSOC-SKU' => 50,
			'VSB-SKU' => 50,
			'SKU-334-24' => 50,
			'SKU-354-24' => 50,
			'SKU-343-24' => 50,
			'SKU-334-354' => 50,
			'SKU-3487604' => 50,
			'SKU-5346-6' => 50,
			'SKU-334-24235' => 50,
			'SKU-33400-24' => 50,
			'SKU-334-2423500' => 50,
			'SKU-33544-4324' => 50
		);*/
		
		$testItems = array(
			'diana-001' => 30,
			'123546-blue-0-3M' => 100,
			'diana-002' => 10,
			'dotcom-test-01' => 40,
			'dotcom-test-02' => 25,
			'dotcom-test-03' => 90,
			'dotcom-test-04' => 50,
			'dotcom-test-05' => 50
		);
	
		return $this->submitPurchaseOrdersToDotcom($testItems);
	}
	
	public function testSubmitOrdersToFulfill() {
		//$orders = Mage::getModel('sales/order')->getCollection()
											   //->addAttributeToFilter('state', Mage_Sales_Model_Order::STATE_PROCESSING);
		//$orders = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('entity_id', array('in' => array(94)));
		//$orders = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('entity_id', array('in' => array(187, 189, 190)));
		//echo count($orders);
		//return $this->submitOrdersToFulfill($orders);
		
		$this->submitOrderToFulfillByQueue();
	}
}
