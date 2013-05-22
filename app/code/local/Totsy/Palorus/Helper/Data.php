<?php
/**
 * @category    Totsy
 * @package     Totsy_Palorus_Helper_Data
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */
 
class Totsy_Palorus_Helper_Data extends Litle_Palorus_Helper_Data
{

    public function getBaseUrl()
    {
        $url = Mage::getModel('creditcard/paymentLogic')->getConfigData('url');
        return self::getBaseUrlFrom($url);
    }

    /**
     * Returns the logged in user.
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        $customer = null;
        if($this->getQuote()->getId()) {
            $customer = $this->getQuote()->getCustomer();
        } else {
            $orderId = Mage::app()->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        }
        return $customer;
    }

    /**
     * Get the card from vault that has been used the last
     *
     * @return vault_id
     */
    public function getLastCardUsed() {
        $customer = Mage::helper('palorus')->getCustomer();
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addAttributeToSort('created_at', 'DSC');
        foreach($orders as $_order) {
            if($_order->getPayment()->getData('litle_vault_id')) {
                $card = Mage::getModel('palorus/vault')->load($_order->getPayment()->getData('litle_vault_id'));
                if($card->getId()) {
                    return $card;
                }
            }
            if($_order->getPayment()->getData('cybersource_subid')) {
                $card = Mage::getModel('paymentfactory/profile')
                    ->load($_order->getPayment()->getData('cybersource_subid'), 'subscription_id');
                if($card->getId()) {
                    return $card;
                }
            }
        }
        return null;
    }
}
