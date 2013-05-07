<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lhansen
 * Date: 4/10/13
 * Time: 5:30 PM
 * To change this template use File | Settings | File Templates.
 */

class Totsy_Fulfillment_Helper_Newgistics extends Totsy_Fulfillment_Helper_Data  {
    /**
     * newgistics xml definition sheets
     */
    const YAML_PRODUCT_FILENAME = 'Product';
    const YAML_MANIFEST_FILENAME = 'Manifest';
    const YAML_ORDER_FILENAME = 'Order';
    /**
     * order shipment states
     */
     const ORDER_SHIPMENT_PARTIAL = 'partial_ship';
     const ORDER_SHIPMENT_FULL = 'full_ship';
     const ORDER_SHIPMENT_ZERO = 'zero_ship';


    /**
     * @param $products
     * @throws Exception
     * @internal param $ <array Mage_Catalog_Model_Product|int> $products
     * @return mixed
     * @see Totsy_Fulfillment_Model_Provider_Newgistics::submitProducts
     * @see Totsy_Fulfillment_Helper_Data::createXmlDoc
     */
    public function productToXml($products) {
        $newgisticsProducts = array();

        $file = dirname(__FILE__) . DS . 'Newgistics' .DS . self::YAML_PRODUCT_FILENAME . '.yaml';

        $xml_structure = yaml_parse_file($file);
        
        if(is_array($products)) {
            foreach($products as $product) {
				
				if(is_int($product)) {
					$product = Mage::getModel('catalog/products')->load($product);
				}
				$productId = $product->getData('id');
				if(empty($productId)) {
					continue;
				}
                $temp = array();
                foreach($xml_structure['product'] as $key => $value) {
                    if($key == 'customFields') {
                        $temp[$key] = array();
                        foreach($value as $field => $correlation) {
                            $temp[$key][$field] = $product->getData($correlation);
                        }
                    } else {
                        $temp[$key] = $product->getData($value);
                    }
                }
                $newgisticsProducts['product'][] = $temp;
            }
        } else {
            $temp = array();
            $products = Mage::getModel('catalog/product')->load($products);
            
            if(is_int($products)) {
				$products = Mage::getModel('catalog/products');
			}
			$productId = $products->getData('id');

            if(empty($productId)) {
                throw new Exception("Invalid product. Please provide a valid product");
            }
            
            foreach($xml_structure['product'] as $key => $value) {
                if($key == 'customFields') {
                    $temp[$key] = array();
                    foreach($value as $field => $correlation) {
                        $temp[$key][$field] = $products->getData($correlation);
                    }
                } else{
                    $temp[$key] = $products->getData($value);
                }
            }
            $newgisticsProducts['product'][] = $temp;
        }
        $xmlresult = $this->createXMLDoc('products',$newgisticsProducts);
        $xmlresult->addAttribute('apiKey', Totsy_Fulfillment_Model_Provider_Newgistics::API_KEY);
        return $xmlresult->asXML();
    }

    /**
     * @param Totsy_Fulfillment_Purchaseorder $purchaseorder
     * @return mixed
     *
     * @see Totsy_Fulfillment_Model_Provider_Newgistics::submitPurchaseOrder
     * @see Totsy_Fulfillment_Helper_Data::createXmlDoc
     */
    public function purchaseorderToXml($purchaseorder) {
        $manifestArray = array();
        $file = dirname(__FILE__) . DS . 'Newgistics' .DS . self::YAML_MANIFEST_FILENAME . '.yaml';
        $xml_structure = yaml_parse_file($file);

        $manifest = $xml_structure['manifest'];

        $temp['manifest_slip'] = array();
        foreach($manifest['manifest_slip'] as $key => $value) {
            $temp['manifest_slip'][$key] = $purchaseorder->getData($value);
        }

        $temp['contents']['item'] = array();

        foreach($purchaseorder->getData('items') as $item) {
            $temp_items = array();
            foreach($manifest['contents']['item'] as $key => $value) {
                $temp_items[$key] = $item[$value];

            }
            $temp['contents']['item'][] = $temp_items;
        }
        $manifestArray['manifest'][] = $temp;
        $xmlresult = $this->createXMLDoc('manifests', $manifestArray);
        $xmlresult->addAttribute('apiKey', Totsy_Fulfillment_Model_Provider_Newgistics::API_KEY);
        return $xmlresult->asXML();
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @return mixed
     * @see Totsy_Fulfillment_Model_Provider_Newgistics::submitOrders
     * @see Totsy_Fulfillment_Helper_Data::createXmlDoc
     */
    public function orderToXml($order) {
        $file = dirname(__FILE__) . DS . 'Newgistics' .DS . self::YAML_ORDER_FILENAME . '.yaml';
        $xml_structure = yaml_parse_file($file);
        $orderArray = array();

        $orderStructure = $xml_structure['Order'];

        $temp['CustomerInfo'] = array();
        foreach($orderStructure as $key => $value) {
            if($key == 'CustomerInfo'){
                $shippingInfo = $order->getShippingAddress();
                foreach($value as $sub_key => $sub_value) {
    
                    switch($sub_key) {
                        case 'FirstName':
                        case 'LastName':
                            $temp['CustomerInfo'][$sub_key] = $shippingInfo->getData($sub_value);
                            break;
                        case 'Address1':
                        case 'Address2':
                            $temp['CustomerInfo'][$sub_key] = $shippingInfo->getStreet($sub_value);
                            break;
                        case 'State':
                            $temp['CustomerInfo'][$sub_key] = Mage::helper('fulfillmentfactory')->getStateCodeByFullName($shippingInfo->getData($sub_value), $shippingInfo->getCountry());
                            break;
                        case 'City':
                        case 'Zip':
                        case 'Country':
                            $temp['CustomerInfo'][$sub_key] = $shippingInfo->getData($sub_value);
                            break;
                        default:
                            $temp['CustomerInfo'][$sub_key] = $order->getData($sub_value);
                            break;
                    }
                }
            } elseif($key == 'Items') {
                $temp[$key] = array();
                $items = $order->getAllItems();

                foreach($items as $item) {
                    if((boolean)$item->getIsVirtual() || $item->getProductType() == 'configurable'){
                        continue;
                    }
                    foreach($value as $sub_key => $sub_value) {
                        $temp_item = array();
                        foreach($sub_value as $k => $v) {
                            $temp_item[$k] = $item->getData($v);
                            if($k == 'Qty'){
                                $temp_item[$k] = $item->getQtyToShip($v);
                            }elseif($k == 'CustomFields') {
                                $temp_item[$k] = array();
                                foreach($v as $field => $correlation) {
                                    $temp_item[$k][$field] = $item->getData($correlation);
                                    if($field == 'Price'){
                                       $temp_item[$k][$field] = number_format($item->getPrice(), 2, '.', '');
                                    }
                                }
                            }
                        }
                    }
                    $temp[$key]['item'][] = $temp_item;
                }
            }elseif($key == '_attribute'){
                $temp[$key] = array();
                foreach($value as $sub_key => $sub_value ){
                    $temp[$key][$sub_key] = $order->getData($sub_value);
                }
            } else {
                $temp[$key] = var_export($order->getData($value), true);
            }
        }
        $temp['AllowDuplicate'] = var_export($orderStructure['AllowDuplicate'], true);
        $temp['CustomerInfo']['isResidential'] = var_export($orderStructure['CustomerInfo']['isResidential'], true);
        $temp['OrderDate'] = date("Y-m-d", strtotime($order->getData($orderStructure['OrderDate'])));
        $orderArray['Order'][] = $temp;
        
        $xmlresult = $this->createXMLDoc('Orders', $orderArray);
        $xmlresult->addAttribute('apiKey', Totsy_Fulfillment_Model_Provider_Newgistics::API_KEY);
        return $xmlresult->asXML();
    }

    /**
     * @param mixed $xmlData
     * @return array<Mage_Sales_Model_Shipment>
     *
     * @see Totsy_Fulfillment_Model_Provider_Newgistics::getShipment
     * @see Totsy_Fulfillment_Helper_Newgistics::orderShipmentState
     */
    public function processShipmentXml($xmlData) {
        $arrayShipments = array();

        foreach($xmlData as $shipment) {
			
			$shippedItems = $shipment->Items;
			$order = Mage::getModel('sales/order')->load($shipment->OrderID, 'increment_id');
			if(!$order->canShip() || $order->getStatus() == 'partially_shipped'){
				continue;
			}
			
			$shipmentCarrier = "";
            $itemQtys = array();
            foreach ($shipment->Items as $shipmentItem) {
                $shipmentCarrier = (string) $shipmentItem->ShipMethod;
                $orderItem = $order->getItemById($shipmentItem->CustomFields->Id);
                if($orderItem && $orderItem->canShip()) {
                    $itemQtys[$orderItem->getId()] = $shipmentItem->Qty;
                }
            }

            $shipmentData = array(
                'total_weight' => (string) $shipment->Weight,
                'carrier_code' => $shipmentCarrier,
            );
			
			
			//store ship records
			$shipmentRecord = Mage::getModel('sales/service_order', $order)
                    ->prepareShipment($itemQtys);

			// create a new shipment track item
			$shipmentTrack = Mage::getModel('sales/order_shipment_track')
				->addData($shipmentData);
				
			try {
				$shipmentRecord->addData($shipmentData)
					->addTrack($shipmentTrack)
					->register()
					->save();
				$order->setDataChanges(true);
				$order->save();
				$arrayShipments[] = $shipmentRecord;
			} catch(Exception $e) {
				Mage::logException($e);
				continue;
			}
			
			$shipmentState = $this->orderShipmentState($shippedItems, $order);
			//update itemqueue
			switch($shipmentState) {
				case self::ORDER_SHIPMENT_ZERO:
					$order->setStatus("partially_shipped")->save();
					break;
				case self::ORDER_SHIPMENT_PARTIAL:
					$order->setStatus("partially_shipped")->save();
					break;
			}
			
			if (!$shipment->getEmailSent()) {
				$shipment->sendEmail()
					->setEmailSent(true)
					->save();
			}
		}
			
        return $arrayShipments;
    }

    /**
     * Detects if the order was partially shipped, fully shipped, or 
     * failed shipped based on received shipment information
     * @param $xmlData
     * @param $order
     *
     * @return string
     * @see Totsy_Fulfillment_Helper_Newgistics::processShipmentXml
     */
    public function orderShipmentState($xmlData, $order) {
		$shipItemCount = count($xmlData->children());
		if( $shipItemCount == 0) {
			return self::ORDER_SHIPMENT_ZERO;
		}
		
		$status = self::ORDER_SHIPMENT_FULL;
		foreach($xmlData as $item) {
			$itemID = $item->CustomFields->Id;
			$itemQueue = Mage::getModel('fulfillmentfactory/itemqueue')
				->loadByItemId($itemID);
			$orderline = $order->getItemById($itemID);
			$orderQty = $orderline->getQtyToShip();
			$parent = $orderline->getParentItem();
			
			if($parent) {
				$orderQty = $parent->getQtyToShip();
			}
            $itemQueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_CLOSED);
			if($orderQty > $shipItemCount){
				$itemQueue->setStatus(Harapartners_Fulfillmentfactory_Model_Itemqueue::STATUS_SHIPMENT_ERROR);
				$status = self::ORDER_SHIPMENT_PARTIAL;
			}
			$itemQueue->save();
		}
		return $status;
    }

    /**
     * Prcoesses the xml received from Totsy_Fulfillment_Model_Providers_Newgistics::getReceipt()
     * @param $xmlData
     * @return array <Totsy_Fulfillment_Receipt>
     */
    public function processManifestReceipt($xmlData) {

        $arrayReceipt = array();

        foreach($xmlData->manifests as $receipt) {
            $tempReceipt = Mage::getModel('fulfillment/receipt');

            $tempReceipt->setData('provider', 'atlast');
            $tempReceipt->setData('po_number', $receipt->manifest_slip->manifest_po);
            $tempReceipt->setData('warehouse_location', $receipt->manifest_slip->destination_warehouse);
            $tempReceipt->setData('status', $receipt->manifest_slip->status);
            $tempReceipt->setData('po_sent_date', date('Y-m-d H:i:s', strtotime($receipt->manifest_slip->created_date)));
            $tempReceipt->setData('warehouse_arrival_date', date('Y-m-d H:i:s', strtotime($receipt->manifest_slip->actual_arrival_date)));
            $tempReceipt->setData('cargo_received_date', date('Y-m-d H:i:s', strtotime($receipt->manifest_slip->actual_received_date)));
            $tempReceipt->setData('created_date', date('Y-m-d H:i:s'));
            $tempReceipt->setData('updated_date', date('Y-m-d H:i:s'));

            $total_no_units = 0;
            $damage_units = 0;

            if($receipt->contents){
                foreach($receipt->contents as $item) {
                    $total_no_units += $item->received_qty;
                    $damage_units += $item->damage_qty;
                }
            }

            $tempReceipt->setData('units_received', $total_no_units);
            $tempReceipt->setData('damaged_units_received', $total_no_units);

            $tempReceipt->save();
            $arrayReceipt[] = $tempReceipt;
        }
        return $arrayReceipt;
    }
}
