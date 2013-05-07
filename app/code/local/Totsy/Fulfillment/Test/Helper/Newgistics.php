<?php
/**
 * @author Lawrenberg Hanson <lhanson@totsy.com>
 *
 * Totsy_Fulfillment_Helper_Newgistics Test
 */

class Totsy_Fulfillment_Test_Helper_Newgistics
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     * @loadExpectation convertProductToXml
     * @dataProvider dataProvider
     */
    public function convertProductToXml($products, $expectations){
        $testProducts = array();

        if (count($products > 1)) {
            foreach($products as $product) {
                $temp = Mage::getModel('catalog/product');
                foreach($product as $key => $value) {
                    $temp->setData($key, $value);
                }
                $testProducts[] = $temp;
            }
        } else {
            $temp = Mage::getModel('catalog/product');

            foreach($product[0] as $key => $value) {
                $temp->setData($key, $value);
            }

            $testProducts[] = $temp;
        }
        
        $actual = Mage::helper('fulfillment/newgistics')->productToXml($testProducts);
        $expected = $this->expected($expectations)->getValue();
        $expected = new SimpleXmlElement(trim($expected));
        $this->assertXmlStringEqualsXmlString($expected->asXML(), $actual);
    }

    /**
     * @test
     * @dataProvider dataProvider
     * @loadExpectation
     */
    public function convertPurchaseOrderToXml($purchaseOrder, $expectations) {

        $testPurchaseOrder = Mage::getModel('fulfillment/purchaseorder');
        $testPurchaseOrder->setData($purchaseOrder['purchase_order']);
        $actual = Mage::helper('fulfillment/newgistics')->purchaseorderToXml($testPurchaseOrder);
        $expected = $this->expected($expectations)->getValue();
        $expected = new SimpleXmlElement($expected);
        $this->assertXmlStringEqualsXmlString($expected->asXML(), $actual);
    }

    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     * @loadExpectation
     * @dataProvider dataProvider
     *
     * @see Totsy_Fulfillment_Helper_Newgistics::orderToXml
     */
    public function convertOrderToXml($order, $expectations) {
        $testOrder = Mage::getModel('sales/order')->load($order['order_id']);
        $actual = Mage::helper('fulfillment/newgistics')->orderToXml($testOrder);
        $expected = $this->expected($expectations)->getValue();
        $expected = new SimpleXmlElement($expected);
        $this->assertXmlStringEqualsXmlString($expected->asXML(), $actual);
    }

    /**
     * @test
     * @loadFixture
     * @doNotIndexAll
     * @loadExpectation
     * @dataProvider dataProvider
     */
    public function processShipmentFiles($shipmentFile, $expectations){

    }
}
