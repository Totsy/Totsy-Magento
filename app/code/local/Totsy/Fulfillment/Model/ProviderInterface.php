<?php
/**
 * @category    Totsy
 * @package     Totsy_Fulfillment_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

interface Totsy_Fulfillment_Model_ProviderInterface
{
    /**
     * @param $purchaseOrder Totsy_Fulfillment_Model_PurchaseOrder|int
     *
     * @return bool
     */
    public function submitPurchaseOrder($purchaseOrder);

    /**
     * @param array $options
     *
     * @return array<Totsy_Fulfillment_Model_Receipt>
     */
    public function getReceipts(array $options);

    /**
     * @param $product Mage_Catalog_Model_Product|int
     *
     * @return int
     */
    public function getInventory($product);

    /**
     * @param $order Mage_Sales_Model_Order|int
     *
     * @return bool
     */
    public function submitOrder($order);

    /**
     * @return array<Mage_Sales_Model_Shipment>
     */
    public function getShipments();
}
