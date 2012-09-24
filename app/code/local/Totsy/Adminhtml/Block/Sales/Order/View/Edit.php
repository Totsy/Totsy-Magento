<?php
/**
 * @category    Totsy
 * @package     Totsy_Adminhtml_Block_Sales_Order
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Adminhtml_Block_Sales_Order_View_Edit
    extends Mage_Adminhtml_Block_Sales_Order_View_Edit
{
    public function customerHasAddresses()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        return count($customer->getAddresses());
    }


    public function getAddressesHtmlSelect($type)
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        $options = array();
        foreach ($customer->getAddresses() as $address) {
            $options[] = array(
                'value' => $address->getId(),
                'label' => $address->format('oneline')
            );
        }

        $addressId = $order->getAddress()->getBillingAddressId();

        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'_address_id')
            ->setId($type.'-address-select')
            ->setClass('address-select')
            ->setExtraParams('onchange="'.$type.'.newAddress(!this.value)"')
            ->setValue($addressId)
            ->setOptions($options);

        $select->addOption('', Mage::helper('checkout')->__('New Address'));

        return $select->getHtml();
    }
}