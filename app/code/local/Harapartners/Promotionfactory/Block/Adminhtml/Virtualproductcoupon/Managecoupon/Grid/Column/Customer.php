<?php

/**
 * @category    Harapartners
 * @package     Harapartners_Promotionfactory_Block_Adminhtml_Virtualproductcoupon_Managecoupon_Grid_Column_Customer
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */
class Harapartners_Promotionfactory_Block_Adminhtml_Virtualproductcoupon_Managecoupon_Grid_Column_Customer
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Render Customer Email linked to its customer edit page
     *
     * @param   Varien_Object $row
     * @return  string
     */
    protected function _getValue(Varien_Object $row)
    {
        if (!$row->getOrderId()) {
            return '';
        }
        $order = Mage::getModel('sales/order')->load($row->getOrderId());
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        return '<a href="' . $this->getUrl('adminhtml/customer/edit', array('id' => $order->getCustomerId())) . '">'
            . $customer->getEmail() . '</a>';
    }
}
