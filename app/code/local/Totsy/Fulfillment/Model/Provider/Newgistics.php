<?php
/**
 * @category    Totsy
 * @package     Totsy_Fulfillment_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

class Totsy_Fulfillment_Model_Provider_Newgistics
    implements Totsy_Fulfillment_Model_ProviderInterface
{
    /**
     * @param $purchaseOrder Totsy_Fulfillment_Model_PurchaseOrder|int
     *
     * @return bool
     */
    public function submitPurchaseOrder($purchaseOrder)
    {
        // TODO: Implement submitPurchaseOrder() method.
    }

    /**
     * @param array $options
     *
     * @return array<Totsy_Fulfillment_Model_Receipt>
     */
    public function getReceipts(array $options)
    {
        // TODO: Implement getReceipts() method.
    }

    /**
     * @param $product Mage_Catalog_Model_Product|int
     *
     * @return int
     */
    public function getInventory($product)
    {
        // TODO: Implement getInventory() method.
    }

    /**
     * @param $order Mage_Sales_Model_Order|int
     *
     * @return bool
     */
    public function submitOrder($order)
    {
        // TODO: Implement submitOrder() method.
    }

    /**
     * @return array<Mage_Sales_Model_Shipment>
     */
    public function getShipments()
    {
        // TODO: Implement getShipments() method.
    }
}
