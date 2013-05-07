<?php
/**
 * @category    Totsy
 * @package     Totsy_Fulfillment_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

require_once Mage::getBaseDir('base') . "/vendor/autoload.php";
use Guzzle\Http\Client;

class Totsy_Fulfillment_Model_Provider_Newgistics implements Totsy_Fulfillment_Model_ProviderInterface
{
    //TODO: this is needs to be stored in the magento core config table
    const API_URL = "http://staging.api.atlastfulfillment.com/";
    const API_KEY = "2f05718eda64482e9743c6c57f1db161";

    const RECEIPT_STATUS_CREATED = 'CREATED';
    const RECEIPT_STATUS_UPDATED = 'UPDATED';
    const RECEIPT_STATUS_SHIPPED = 'SHIPPED';
    const RECEIPT_STATUS_ARRIVED =  'ARRIVED';
    const RECEIPT_STATUS_RECEIVED = 'RECEIVED';
    const RECEIPT_STATUS_CANCELED = 'CANCELED';
    const RECEIPT_STATUS_PACKAGING = 'PACKAGING';

    public $httpClient = null;

    public function __construct() {
		$this->httpClient = new Client();
	}

    /**
     * @param $purchaseOrder Totsy_Fulfillment_Model_PurchaseOrder|int
     *
     * @return bool
     */
    public function submitPurchaseOrder($purchaseOrder)
    {
        $success = false;
        try {
            $requestPath = 'post_manifests.aspx';
            if(is_int($purchaseOrder)) {
				$purchaseOrder = Mage::getModel('stockhistory/purchaseorder');
			}
            $poId = $purchaseOrder->getId();

            if(empty($poId)) {
                throw new Exception("Invalid purchase order. Please provide a valid purchase order");
            }
            
            $xml = Mage::helper('fulfillment/newgistics')->purchaseorderToXml($purchaseOrder);
            $responseXml = $this->_postData($requestPath, $xml);

            if($responseXml->errors->children()) {
                Mage::log($responseXml->errors->children(), 'newgistics_errors.log');
                Mage::getSingleton('adminhtml/session')->addError($responseXml->manifests->errors);
                $success = false;
            } else {
                $success = true;
                Mage::getSingleton('adminhtml/session')->addSuccess('All purchase orders were successfully created at AtLast');
            }
        } catch(Exception $e) {
            Mage::log($e->getMessage(), 'newgistics_errors.log');
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        return $success;
    }

    /**`
     * @param array $options
     *
     * @return array<Totsy_Fulfillment_Model_Receipt>
     */
    public function getReceipts(array $options)
    {
        try{
            $requestPath = 'manifests.aspx';
            $receipts = array();
            $_default = array(
                'po' => null,
                'warehouseID' => null,
                'status' => self::RECEIPT_STATUS_RECEIVED,
                'sku' => null,
                'supplier' => null,
                'createdStartTimestamp' => null,
                'createdEndTimestamp' => null,
                'arrivalStartTimestamp' => null,
                'arrivalEndTimestamp' => null,
                'receivedStartTimestamp' => null,
                'receivedEndTimestamp' => null,
                'returnLineItems' => true
            );

            $options = array_merge($_default, $options);

            $responseXml = $this->_getData($requestPath, $options);

            if(count($responseXml->manifests->children()) > 0){
                $receipts = Mage::helper('fulfillment/newgisitics')->convertManifestReceiptXml($responseXml);
            } else {
                Mage::log("getReciept: A purchase order(s) receipt does not exist at Atlast ", 'newgistics_errors.log');
            }
        } catch(Exception $e) {
            Mage::log("getReciept: An error has occured.  " . $e->getMessage(), Zend_Log::DEBUG, 'newgistics_error.log');
        }
        return $receipts;
    }

    /**
     * @param $product Mage_Catalog_Model_Product|int
     *
     * @return int
     */
    public function getInventory($product)
    {
        $quantity = 0;

        try {

			$requestPath = 'inventory_details.aspx';

            if (is_int($product)) {
                $product = Mage::getModel('catalog/product')->load($product);
            }

            $productId = $product->getData('entity_id');

            if(empty($productId)) {
                throw new Exception("Invalid product. Please provide a valid product");
            }
            
            $params = array(
				'sku' => $product->getData('sku'),
				'type' => 'INVENTORY'
			);

            $responseXml = $this->_getData($requestPath, $params);
            if($responseXml->inventories){
				$quantity = (int)$responseXml->inventories->inventory->quantity;
			}
            if($responseXml->response->errors) {
                foreach($responseXml->response->errors as $error) {

                }
                Mage::log("getInventory: " .  $error, Zend_Log::DEBUG ,'newgistics_errors.log');
            }

        }catch(Exception $e) {
            Mage::log("getInventory: " .  $e->getMessage(), Zend_Log::DEBUG ,'newgistics_errors.log');
        }


        return $quantity;
    }

    /**
     * @param $order Mage_Sales_Model_Order|int
     *
     * @return bool
     */
    public function submitOrder($order)
    {
       try {
            $requestPath = 'post_manifests.aspx';
            $xml = Mage::helper('fulfillment/newgistics')->orderToXml($order);

            $reponseXml = $this->_postData($requestPath, $xml);

            if($responseXml->manifests->errors) {
                Mage::log($responseXml->manifests->errors->children(), 'newgistics_errors.log');
            } else {
                $success = true;
            }
        } catch(Exception $e) {
            Mage::log($e->getMessage(), 'newgistics_errors.log');
        }
    }

    /**
     * @return array<Mage_Sales_Model_Shipment>
     */
    public function getShipments()
    {
        try {
            $requestPath = 'manifests.aspx';
            $shippedOrders = array();
            $fromDate = date('Y-m-d H:i:s', strtotime('-1 day'));
            $toDate = date('Y-m-d H:i:s');
            $params = array(
				'startShippedTimestamp' => $fromDate,
				'endShippedTimestamp' => $toDate
            );
            $responseXml = $this->_getData($requestPath, $params);
            
            if (count($responseXml->children()) == 0) {
                Mage::log('No shipments found.' ,Zend_Log::DEBUG ,'newgistics_errors.log');
            } else {
                $shippedOrders = Mage::helper('fulfillment/newgistics')->processShipmentXml($responseXml);
            }
        } catch(Exception $e) {
            Mage::log($e->getMessage(),Zend_Log::DEBUG ,'newgistics_errors.log');
        }
        
        return $shippedOrders;
    }

    /**
     * @param array $product Mage_Catalog_Model_Product|int
     * @return bool
     */
    public function submitProducts($product)
    {
        $success = false;

        try{
            $requestPath = 'post_products.aspx';
            			
            $xml = Mage::helper('fulfillment/newgistics')->productToXml($product);

            $responseXml = $this->_postData($requestPath, $xml);
            if($responseXml->errors->children()) {
                foreach($responseXml->errors->children() as $error) {
                    Mage::log('Product Submission: \n\t Sku :' . $error['sku'] . ' ' . $error, Zend_Log::DEBUG,
                        'newgistics_errors.log');
                }
            }else{
                $success = true;
            }

        }catch(Exception $e){
            Mage::log("submitProducts: " . $e->getMessage(), Zend_Log::DEBUG ,'newgistics_errors.log');
        }

        return $success;
    }

    /**
     * @param string $path request path
     * @param xmal $data data to be sent to web service
     * @return xml response
     * @throws Exception
     */
    protected function _postData($path, $data) {
        $apiCall = self::API_URL . $path;

        $client = $this->httpClient;
        $response = $client->post($apiCall, array('content-type', 'text/xml'), $data)->send();
        $statusCode = $response->getStatusCode();

        if($statusCode != 200) {
            throw new Exception("Unable to reach AtLast Newgistics.  Please try again later. Received status code: {$statusCode}");
        }

        return $response->xml();
    }

    /**
     * @param string $path request path
     * @param xml $params data to be sent to web service
     * @return xml response
     * @throws Exception
     */
    protected function _getData($path, $params) {

        $apiCall = self::API_URL . $path;
		$client = $this->httpClient;
        $paramKey = "?key=" . self::API_KEY;
        $paramKey = $paramKey . http_build_query($params);
        $response = $client->get($apiCall . '&' . $paramKey)->send();
        $statusCode = $response->getStatusCode();

        if($statusCode != 200) {
            throw new Exception("Unable to reach AtLast Newgistics.  Please try again later. Received status code: {$statusCode}");
        }

        return $response->xml();
    }
}
