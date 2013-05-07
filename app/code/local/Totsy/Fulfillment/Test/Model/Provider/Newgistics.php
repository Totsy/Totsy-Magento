<?php
/**
 * @category    Totsy
 * @package     Totsy_Fulfillment_Test
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */
 
require_once Mage::getBaseDir('base') . "/vendor/autoload.php";

class Totsy_Fulfillment_Test_Model_Provider_Newgistics
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @loadExpectation productPost
     * @dataProvider dataProvider
     */
    public function productPost($products, $expectation, $statusCode, $responseXml) {
        $newgistics = Mage::getModel('fulfillment/provider_newgistics');

        $test_products = array();

        if (count($products > 1)) {
            foreach($products as $product) {
                $temp = Mage::getModel('catalog/product');
                foreach($product as $key => $value) {
                    $temp->setData($key, $value);
                }
                $test_products[] = $temp;
            }
        } else {
            $temp = Mage::getModel('catalog/product');

            foreach($product[0] as $key => $value) {
                $temp->setData($key, $value);
            }

            $test_products[] = $temp;
        }

        $plugin = new Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(new Guzzle\Http\Message\Response($statusCode, array(
                'Content-Type' => 'text/xml',
                'Cache-Control' => 'private',
                'Server' => 'Microsoft-IIS/7.5'
            ),
            $responseXml));
        $newgistics->httpClient->addSubscriber($plugin);
        
        $response = $newgistics->submitProducts($test_products);

        $this->assertEquals($this->expected($expectation)->getResponse(), $response, "Product Submission Failed!");
    }


    /**
     *
     * @loadExpectation retrieveShipment
     * @dataProvider dataProvider
     */
    public function submitPurchaseOrderPost($purchase_order, $expectation) {
        $newgistics = Mage::getModel('fulfillment/provider_newgistics');

        $test_purchaseorder = Mage::getModel('fulfillment/purchaseorder');
        $test_purchaseorder->setData($purchase_order['purchase_order']);
        
		$plugin = new Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(new Guzzle\Http\Message\Response($this->expected($expectation)->getStatusCode(), array(
            'Content-Type' => 'text/xml',
            'Cache-Control' => 'private',
            'Server' => 'Microsoft-IIS/7.5'
        ), $this->expected($expectation)->getResponseXml()
        ));
        $newgistics->httpClient->addSubscriber($plugin);

        $response = $newgistics->submitPurchaseOrder($test_purchaseorder);
        $this->assertEquals($this->expected($expectation)->getResponse(), $response, "Purchase Order Failed");
    }

    /**
     * @test
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function receivedReceipt($params,$expectation,$statusCode, $responseXml) {
        $newgistics = Mage::getModel('fulfillment/provider_newgistics');
		
		$plugin = new Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(new Guzzle\Http\Message\Response($statusCode, array(
            'Content-Type' => 'text/xml',
            'Cache-Control' => 'private',
            'Server' => 'Microsoft-IIS/7.5'
        ), $responseXml));
        $newgistics->httpClient->addSubscriber($plugin);

        $response = $newgistics->getReceipts($params);
        $this->assertContainsOnlyInstancesOf($this->expected($expectation)->getResponse(), $response, "Wrong Purchase Order return or no purchase order was returned");
    }

    /**
     * @param $response_code
     * @param $response_xml
     * @param $expectation
     * 
     * @
     * @loadFixture
     * @loadExpectation
     * @doNotIndexAll
     * @dataProvider dataProvider
     */

    public function retrieveShipment($response_code, $response_xml,$expectation) {
        $newgistics = Mage::getModel('fulfillment/provider_newgistics');


        $response_xml = new SimpleXmlElement(trim($response_xml));
        $response_xml = $response_xml->asXml();

        $plugin = new Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(new Guzzle\Http\Message\Response($response_code, array(
            'Content-Type' => 'text/xml',
            'Cache-Control' => 'private',
            'Server' => 'Microsoft-IIS/7.5'
        ), $response_xml));
        $newgistics->httpClient->addSubscriber($plugin);

        $response = $newgistics->getShipments();
        $ship = Mage::getModel('sales/order')->load($this->expected($expectations)->getId(), 'increment_id');

        $this->assertContainsOnlyInstancesOf($this->expected($expectations)->getResponse(), $response, 'One of the items in the array is not Mage_Sales_Order_Shipment');
        $this->assertEquals($this->expected($expectations)->getId(), $ship->getData('entity_id'), 'Shipment record was not save in DB');
    }

    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function retrieveInventory($product,$expectation, $response_code, $responseXml){
        $newgistics = Mage::getModel('fulfillment/provider_newgistics');
        
        if(is_array($product)) {
			$temp = Mage::getModel('catalog/product');

            foreach($product[0] as $key => $value) {
                $temp->setData($key, $value);
            }

            $product = $temp;
		}

        $plugin = new Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(new Guzzle\Http\Message\Response($response_code, array(
            'Content-Type' => 'text/xml',
            'Cache-Control' => 'private',
            'Server' => 'Microsoft-IIS/7.5'
        ), $responseXml));

        $newgistics->httpClient->addSubscriber($plugin);

        $response = $newgistics->getInventory($product);

        $this->assertEquals($this->expected($expectation)->getResponse(), $response, '');
    }
    
    /**
     * @
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function productPostLive($products, $expectation) {
        $newgistics = Mage::getModel('fulfillment/provider_newgistics');

        $test_products = array();

        if (count($products > 1)) {
            foreach($products as $product) {
                $temp = Mage::getModel('catalog/product');
                foreach($product as $key => $value) {
                    $temp->setData($key, $value);
                }
                $test_products[] = $temp;
            }
        } else {
            $temp = Mage::getModel('catalog/product');

            foreach($product[0] as $key => $value) {
                $temp->setData($key, $value);
            }

            $test_products[] = $temp;
        }
                
        $response = $newgistics->submitProducts($test_products);

        $this->assertEquals($this->expected($expectation)->getResponse(), $response, "Product Submission Failed!");
    }


    /**
     * @
     * @loadExpectation submitPurchaseOrderPost
     * @dataProvider dataProvider
     */
    public function submitPurchaseOrderPostLive($purchase_order, $expectation) {
        $newgistics = Mage::getModel('fulfillment/provider_newgistics');

        $test_purchaseorder = Mage::getModel('fulfillment/purchaseorder');
        $test_purchaseorder->setData($purchase_order['purchase_order']);
		
        $newgistics->httpClient->addSubscriber($plugin);
        
        $response = $newgistics->submitPurchaseOrder($test_purchaseorder);
        $this->assertEquals($this->expected($expectation)->getResponse(), $response, "Purchase Order Failed");
    }

    /**
     * @test
     * @loadExpectation receivedReceipt
     * @dataProvider dataProvider
     */
    public function receivedReceiptLive($params,$expectation,$statusCode, $responseXml) {
        $newgistics = Mage::getModel('fulfillment/provider_newgistics');
		
        $response = $newgistics->getReceipts($params);
        $this->assertContainsOnlyInstancesOf($this->expected($expectation)->getResponse(), $response, "Wrong Purchase Order return or no purchase order was returned");
    }

    /**
     * @param $response_code
     * @param $response_xml
     * @param $expectation
     * 
     * @
     * @loadFixture
     * @loadExpectation
     * @doNotIndexAll
     * @dataProvider dataProvider
     */

    public function retrieveShipmentLive($response_code, $response_xml,$expectation) {
        $newgistics = Mage::getModel('fulfillment/provider_newgistics');


        $response_xml = new SimpleXmlElement(trim($response_xml));
        $response_xml = $response_xml->asXml();

        $response = $newgistics->getShipments();
        $ship = Mage::getModel('sales/order_shipment')->load(1000,'order_id');
        $this->assertContainsOnlyInstancesOf($this->expected($expectation)->getResponse(), $response, 'Wrong instance returned or empty result');
    }

    /**
     * @test
     * @loadFixture retrieveInventory
     * @doNotIndexAll
     * @loadExpectation retrieveInventory
     * @dataProvider dataProvider
     */
    public function retrieveInventoryLive($product,$expectation, $response_code, $responseXml){
        $newgistics = Mage::getModel('fulfillment/provider_newgistics');

        if(is_array($product)) {
            $temp = Mage::getModel('catalog/product');

            foreach($product[0] as $key => $value) {
                $temp->setData($key, $value);
            }

            $product = $temp;
        }

        $response = $newgistics->getInventory($product);

       $this->assertEquals($this->expected($expectation)->getResponse(), $response, 'Quantity does not match');
    }
}
