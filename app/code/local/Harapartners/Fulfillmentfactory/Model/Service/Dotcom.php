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
    /**
     * Update local inventory statistics with the latest counts from Dotcom.
     *
     * @return void
     */
    public function updateInventory()
    {
        $xmlStreamName = Mage::helper('fulfillmentfactory/dotcom')
            ->getInventory();

        $reader = new XMLReader();
        $reader->open($xmlStreamName, 'utf8');

        $state = 'waiting';
        $sku = -1;
        $qty = -1;
        $count = 0;

        while ($reader->read()) {
            // this node is an opening <item> tag
            if ('item' == $reader->localName &&
                XMLReader::ELEMENT === $reader->nodeType
            ) {
                $state = 'item';

                // this node is an opening <sku> tag
            } else if ('sku' == $reader->localName &&
                XMLReader::ELEMENT == $reader->nodeType &&
                'item' == $state
            ) {
                $sku = $reader->readString();

                // this node is an opening <quantity_available> tag
            } else if ('quantity_available' == $reader->localName &&
                XMLReader::ELEMENT == $reader->nodeType &&
                'item' == $state
            ) {
                $qty = $reader->readString();

                // this node is a closing <item> tag
            } else if ('item' == $reader->localName &&
                XMLReader::END_ELEMENT == $reader->nodeType
            ) {
                $state = 'waiting';

                //stores inventory as eav attribute at product level
                $product = Mage::getModel('catalog/product')
                    ->loadByAttribute('sku', $sku);

                if ($product && $product->getId()) {
                    $currentInventory = $product->getData('fulfillment_inventory');
                    $inventoryDiff = $qty - $currentInventory;

                    if ($inventoryDiff !== 0) {
                        if ($inventoryDiff > 0) {
                            $inventoryDiff = '+' . $inventoryDiff;
                        }

                        Mage::log(
                            "Updated inventory for SKU '$sku' by $inventoryDiff from $currentInventory to $qty units.",
                            Zend_Log::INFO,
                            'fulfillment_inventory.log'
                        );

                        $product->setData('fulfillment_inventory', $qty);
                        $product->getResource()->saveAttribute(
                            $product,
                            'fulfillment_inventory'
                        );
                    }

                    $this->_fulfillOrderItems($product, $qty);
                    $count++;
                }
            }
        }

        Mage::log(
            sprintf("Retrieved and stored inventory updates for %d products", $count),
            Zend_Log::INFO,
            'fulfillment.log'
        );
    }

    protected function _fulfillOrderItems($product, $qty)
    {
        $sku = $product->getSku();
        $qtyAvailable = $qty - Mage::helper('fulfillmentfactory')->getAllocatedCount($sku);

        Mage::log(
            "Attempting to allocate $qty ($qtyAvailable available) inventory for SKU '$sku'",
            Zend_log::DEBUG,
            'fulfillment_allocation.log'
        );

        $itemqueues = Mage::getModel('fulfillmentfactory/itemqueue')
            ->getCollection()
            ->loadIncompleteItemQueueByProductSku($sku);

        foreach ($itemqueues as $item) {
            $qtyFulfilled = $item->getFulfillCount();
            $qtyRequired  = $item->getQtyOrdered() - $item->getFulfillCount();

            // there is no quantity remaining to be allocated
            if (0 === $qtyAvailable) {
                Mage::log("No more inventory available for SKU '$sku' during fulfillment.", Zend_Log::INFO, 'fulfillment.log');
                break;

            // there is sufficient quantity to completely fulfill this item
            } else if ($qtyRequired <= $qtyAvailable) {
                $qtyFulfilled += $qtyRequired;
                $qtyAvailable -= $qtyRequired;
                $item->setStatus(
                    Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_READY
                );

                Mage::log(
                    sprintf(
                        "Shifted %d stock units from available fulfillment inventory to order %s (SKU '%s' now ready)",
                        $qtyFulfilled,
                        $item->getOrderId(),
                        $product->getSku()
                    ),
                    Zend_log::INFO,
                    'fulfillment.log'
                );

            // there is an insufficient quantity available to fulfill this item
            } else {
                $qtyFulfilled += $qtyAvailable;
                $qtyAvailable = 0;
                $status = (0 === $qtyFulfilled)
                    ? Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PENDING
                    : Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_PARTIAL;
                $item->setStatus($status);

                Mage::log(
                    sprintf(
                        "Shifted %d stock units from available fulfillment inventory to order %s (SKU '%s' partially fulfilled)",
                        $qtyFulfilled,
                        $item->getOrderId(),
                        $product->getSku()
                    ),
                    Zend_log::INFO,
                    'fulfillment.log'
                );
            }

            // update the item
            $item->setData('fulfill_count', $qtyFulfilled);
            $item->save();
        }

        // update the available fulfillment inventory
        $product->setData('fulfillment_inventory', $qtyAvailable);
        $product->getResource()->saveAttribute(
            $product,
            'fulfillment_inventory'
        );

        unset($itemqueues);
    }

    /**
     * Perform order shipment retrieval.
     *
     * @return void
     */
    public function runUpdateShipment()
    {
        try {
            //put one day as default
            $fromDate = date('Y-m-d H:i:s', strtotime('-1 day'));
            $toDate = date('Y-m-d H:i:s');

            $ordersShipped = $this->updateShipment($fromDate, $toDate);
            Mage::log(
                sprintf(
                    'Completed processing for %d orders that have been shipped.',
                    count($ordersShipped)
                ),
                Zend_Log::INFO,
                'fulfillment.log'
            );
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR, 'fulfillment.log');
        }
    }

    /**
     * post purchase orders to Dotcom
     *
     * @param Harapartners_Fulfillmentfactory_Model_Purchaseorder $purchaseOrder
     * @param array $items
     * @return response
     */
    public function submitPurchaseOrdersToDotcom($purchaseOrder, $items)
    {
        $category = Mage::getModel('catalog/category')->load(
            $purchaseOrder->getCategoryId()
        );
        list($eventEndDate, $eventEndTime) = explode(' ', $category->getEventEndDate());

        $poNumber = $purchaseOrder->generatePoNumber();

        $xml = '<purchase_orders xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';

        $xml .= <<<XML
            <purchase_order>
                <po-number><![CDATA[$poNumber]]></po-number>
                <priority-date>$eventEndDate</priority-date>
                <expected-on-dock xsi:nil="true" />
                <items>
XML;

        foreach($items as $sku => $qty) {
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

            if(empty($product) || !$product->getId()) {
                continue;
            }

            $productSku = substr($sku, 0, 17);
            $name = Mage::helper('fulfillmentfactory')->removeBadCharacters($name);
            $name = substr($product->getName(), 0, 28);

            $vendorCode = '<manufacturing-code xsi:nil="true" />';
            if ($value = $product->getVendorCode()) {
                $value = Mage::helper('fulfillmentfactory')->removeBadCharacters($value);
                $value = substr($value, 0, 10);
                $vendorCode = '<manufacturing-code>' . $value . '</manufacturing-code>';
            }

            $style = '<style-number xsi:nil="true" />';
            if ($value = $product->getVendorStyle()) {
                $style = '<style-number>' . substr($value, 0, 10) . '</style-number>';
            }

            $color = '<color xsi:nil="true" />';
            if ($value = $product->getAttributeText('color')) {
                $color = '<color>' . substr($value, 0, 5) . '</color>';
            }

            $size = '<size xsi:nil="true" />';
            if ($value = $product->getAttributeText('size')) {
                $size = '<size>' . substr($value, 0, 5) . '</size>';
            }

            $xml .= <<<XML
                    <item>
                        <sku><![CDATA[$productSku]]></sku>
                        <description><![CDATA[$name]]></description>
                        <quantity>$qty</quantity>
                        <upc xsi:nil="true" />
                        <weight>{$product->getWeight()}</weight>
                        <cost xsi:nil="true" />
                        <price xsi:nil="true" />
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
                        $vendorCode
                        $style
                        <short-name xsi:nil="true" />
                        $color
                        $size
                        <long-description xsi:nil="true" />
                    </item>
XML;
        }

        $xml .=    <<<XML
                </items>
            </purchase_order>
        </purchase_orders>
XML;
        $response = Mage::helper('fulfillmentfactory/dotcom')->submitPurchaseOrders($xml);

        return $response;
    }

    /**
     * submit orders to Dotcom for fulfillment, by quantity
     *
     * @param array $orders for orders we want to submit
     * @param boolean $capturePayment    flag to indicate capture payment in this fulfillment
     * @return response
     */
    public function submitOrdersToFulfill($orders, $capturePayment=false) {
        $responseArray = array();

        Mage::log(
            sprintf(
                'Trying to send %d orders for fulfillment.',
                count($orders)
            ),
            Zend_Log::INFO,
            'fulfillment.log'
        );

        $successCount = 0;

        foreach($orders as $order) {
            try {
                $continue = true;
                if(!$capturePayment || ($order->getStatus() == Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED)) {
                    $continue = false;
                }

                if($continue && ($order->canInvoice() === false)) {
                    $continue = false;
                }

                if($continue && (($invoice = $order->prepareInvoice()) == false)) {
                    $continue = false;
                }

                if($continue && (($invoice->register()) === false)) {
                    $continue = false;
                }

                if($continue && $invoice->canCapture()) {
                    $invoice->capture();

                    $order->setStatus('processing');
                    $order->setState('processing');

                    $transactionSave = Mage::getModel('core/resource_transaction');
                    $transactionSave->addObject($invoice);
                    $transactionSave->addObject($invoice->getOrder());
                    $transactionSave->save();
                    if (!$invoice->getOrder()->getEmailSent()) {
                        $invoice->sendEmail(true)
                            ->setEmailSent(true);
                    }
                }
            }
            catch(Exception $e) {
                $order->setStatus(Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED)->save();
                $order->setState(Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED)->save();
                $message = 'Order ' . $order->getIncrementId() . ' could not place the payment. ' . $e->getMessage();
                Mage::helper('fulfillmentfactory/log')->errorLogWithOrder($message, $order->getId());
                /*
                $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

                //send payment failed email
                Mage::getModel('core/email_template')->setTemplateSubject('Payment Failed')
                                                     ->sendTransactional(6, 'support@totsy.com', $customer->getEmail(), $customer->getFirstname());
                */
                //throw new Exception($message);
                continue;
            }

            $orderDate = date("Y-m-d", strtotime($order->getCreatedAt()));
            $shippingMethod = Mage::helper('fulfillmentfactory/dotcom')->getDotcomShippingMethod($order->getShippingMethod());
            $shippingAddress = $order->getShippingAddress();

            //to avoid null object
            if(empty($shippingAddress)) {
                $shippingAddress = Mage::getModel('sales/order_address');
            }

            $customerId = $order->getCustomerId();
            $customer   = Mage::getModel('customer/customer')->load($customerId);

            $shippingName = $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname();

            $state = Mage::helper('fulfillmentfactory')->getStateCodeByFullName($shippingAddress->getRegion(), $shippingAddress->getCountry());

            $city = Mage::helper('fulfillmentfactory')->validateAddressForDC('CITY', $shippingAddress->getCity());

            $country = Mage::helper('fulfillmentfactory/dotcom')->getCountryCodeUsTerritories($state);

            $xml = <<<XML
        <orders xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
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
                <total-tax>{$order->getTaxInvoiced()}</total-tax>
                <total-shipping-handling>{$order->getShippingAmount()}</total-shipping-handling>
                <total-discount xsi:nil="true"/>
                <total-order-amount>{$order->getTotalInvoiced()}</total-order-amount>
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
                    <billing-city><![CDATA[$city]]></billing-city>
                    <billing-state>{$state}</billing-state>
                    <billing-zip>{$shippingAddress->getPostcode()}</billing-zip>
                    <billing-country>{$country}</billing-country>
                    <billing-phone xsi:nil="true"/>
                    <billing-email>{$customer->getEmail()}</billing-email>
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
                // only process root order items
                if ($item->getParentItem()) {
                    continue;
                }

                $quantity = intval($item->getQtyToShip());
                $sku = substr($item->getSku(), 0, 17);

                if($quantity) {

                    $xml .= <<<XML
                    <line-item>
                        <sku>$sku</sku>
                        <quantity>$quantity</quantity>
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
            }

            $xml .= <<<XML
                </line-items>
            </order>
        </orders>
XML;

            //change status
            $order->setStatus(Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PROCESSING_FULFILLMENT)
                  ->save();

            try {
                $response = Mage::helper('fulfillmentfactory/dotcom')->submitOrders($xml);
                $responseArray[] = $response;

                $error = $response->order_error;
                if ($error) {
                    $order->setStatus(Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_FULFILLMENT_FAILED)
                        ->save();

                    Mage::helper('fulfillmentfactory/log')->errorLogWithOrder(
                        $error->error_description,
                        $order->getId()
                    );
                } else {
                    $successCount++;
                }
            } catch(Exception $e) {
                $order->setStatus(Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_FULFILLMENT_FAILED)
                    ->save();

                Mage::helper('fulfillmentfactory/log')->errorLogWithOrder($e->getMessage(), $order->getId());
            }
        }

        Mage::log(
            sprintf(
                'Successfully sent %d orders for fulfillment.',
                $successCount
            ),
            Zend_Log::INFO,
            'fulfillment.log'
        );

        return $responseArray;
    }

    /**
     * Retrieve shipment information from Dotcom and add shipments for
     * completed orders
     *
     * @param string $fromDate Begin range for searching for shipments.
     * @param string $toDate   End range for searching for shipments.
     * @return int The number of orders processed as shipped and completed.
     */
    public function updateShipment($fromDate = '', $toDate = '') {
        if (empty($fromDate)) {
            $fromDate = date('Y-m-d H:i:s', strtotime('-1 hour'));
        }
        if (empty($toDate)) {
            $toDate = date('Y-m-d H:i:s');
        }

        // get data from dotcom
        $dataXML = Mage::helper('fulfillmentfactory/dotcom')->getShipment($fromDate, $toDate);

        $updatedOrders = 0;
        foreach ($dataXML as $shipment) {
            $attr   = $shipment->attributes('i', TRUE);
            $status = (string) $shipment->order_status;
            $order  = Mage::getModel('sales/order')
                ->loadByIncrementId($shipment->client_order_number);

            if (!$order->getId() || $attr['nil'] || 'Shipped' != $status) {
                continue;
            }

            // ensure there is at least one ship item
            $shipmentItems = $shipment->ship_items->children('a', TRUE);
            if (!$shipmentItems) {
                continue;
            }

            // calculate the total quantity shipped, and select the last
            // shipment carrier
            $shipmentQty = 0;
            $shipmentCarrier = "";
            foreach ($shipmentItems as $shipmentItem) {
                $shipmentQty += (int) $shipmentItem->ship_weight;
                $shipmentCarrier = (string) $shipmentItem->carrier;
            }

            $shipmentData = array(
                'total_weight' => (string) $shipment->ship_weight,
                'total_qty'    => $shipmentQty,
                'order_id'     => $order->getId(),
                'carrier_code' => $shipmentCarrier,
            );

            $orderShipments = $order->getShipmentsCollection();
            if(count($orderShipments) > 0) {
                $shipment = $orderShipments->getFirstItem();
            } else {
                $itemQtyArray = array();
                foreach ($order->getAllItems() as $item) {
                    $itemQtyArray[$item->getData('item_id')] = (int) $item->getQtyToShip();
                }

                $shipment = Mage::getModel('sales/service_order', $order)
                    ->prepareShipment($itemQtyArray);

                // create a new shipment track item
                $shipmentTrack = Mage::getModel('sales/order_shipment_track')
                    ->addData($shipmentData);

                // create a new shipment item
                $shipment->addData($shipmentData)
                    ->addTrack($shipmentTrack)
                    ->save();
            }

            // update the order status and save
            $order->setStatus('complete')->save();

            // send a shipment notification to the customer, if one hasn't been
            // sent already
            if (!$shipment->getEmailSent()) {
                $shipment->sendEmail()
                    ->setEmailSent(true)
                    ->save();

                $updatedOrders++;
            }
        }

        return $updatedOrders;
    }
}
