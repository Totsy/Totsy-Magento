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
     * newgistics product xml definition sheet
     */
    const YAML_PRODUCT_FILENAME = 'Product';
    const YAML_MANIFEST_FILENAME = 'Manifest';
    const YAML_ORDER_FILENAME = 'Order';

    /**
     * @param  <array Mage_Catalog_Model_Product|int> $products
     * @return mixed
     */
    public function productToXml($products) {
        $newgisticsProducts = array();

        $file = dirname(__FILE__) . DS . 'Newgistics' .DS . self::YAML_PRODUCT_FILENAME . '.yaml';

        $xml_structure = yaml_parse_file($file);

        if(is_array($products)) {
            foreach($products as $product) {
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
                    if(!$item->getIsVirtual || $item->getProductType == 'simple'){
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
     */
    public function processShipmentXml($xmlData) {

        foreach($xmlData as $shipment) {
            
            
        }
    }

    public function processManifestReceipt($xmlData){

        $arrayReceipt = array();

        foreach($xmlData->manifests as $receipt) {
            $tempReceipt = Mage::('fulfillment/receipt');

            $tempReceipt->setData('provider', 'atlast');
            $tempReceipt->setData('po_number', $receipt->manifest_slip->manifest_po);
            $tempReceipt->setData('warehouse', $receipt->manifest_slip->destination_warehouse);
            $tempReceipt->setData('status', $receipt->manifest_slip->status);
            $tempReceipt->setData('po_sent_date', $receipt->manifest_slip->created_date);
            $tempReceipt->setData('warehouse_arrival_date', $receipt->manifest_slip->actual_arrival_date);
            $tempReceipt->setData('products_received_date', $receipt->manifest_slip->actual_received_date);
            $tempReceipt->setData('created_date', date('Y-m-d H:i:s'));
            $arrayReceipt[] = $tempReceipt;
        }
        return $arrayReceipt;
    }
}
