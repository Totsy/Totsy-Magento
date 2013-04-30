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
    public function setUp() {
        $this->_model = Mage::getModel('fulfillment/provider_newgistics');
    }

    public function tearDown() {
        unset($this->_model);
    }


    /**
     * @test
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function productPost($products, $expectation) {
        $newgistics = $this->_model;

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
        $plugin->addResponse(new Guzzle\Http\Message\Response($this->expected($expectation)->getStatusCode(), array(
                'Content-Type' => 'text/xml',
                'Cache-Control' => 'private',
                'Server' => 'Microsoft-IIS/7.5'
            ),
            $this->expected($expectation)->getResponseXml()));
        $newgistics->httpClient->addSubscriber($plugin);
        
        $response = $newgistics->submitProducts($test_products);

        $this->assertEquals($this->expected($expectation)->getResponse(), $response, "Product Submission Failed!");
    }


    /**
     * @test
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function submitPurchaseOrderPost($purchase_order, $expectation) {
        $newgistics = $this->_model;

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
     *
     */
    public function receivedReceipt($params,$expectation) {
        $newgistics = $this->_model;
		
		$plugin = new Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(new Guzzle\Http\Message\Response($this->expected($expectation)->getStatusCode(), array(
            'Content-Type' => 'text/xml',
            'Cache-Control' => 'private',
            'Server' => 'Microsoft-IIS/7.5'
        ), $this->expected($expectation)->getResponseXml()));
        $newgistics->httpClient->addSubscriber($plugin);

        $response = $newgistics->getReceipts($params);
        $this->assertEquals($this->expected($expectation)->getResponse(), $response, "Wrong Purchase Order return or no purchase order was returned");
    }

    /**
     *
     */
    public function retrieveInventory(){
        $newgistics = $this->_model;
        
        $plugin = new Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(new Guzzle\Http\Message\Response(404));
        $newgistics->httpClient->addsubcriber($plugin);

      //  $response = $newgistics->getInventory($purchase_order);

        $this->assertEquals($this->expected($expectation)->getResponse(), $response, '');
    }
}
