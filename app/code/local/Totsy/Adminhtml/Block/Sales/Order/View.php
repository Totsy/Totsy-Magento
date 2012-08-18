<?php
/**
 * @category    Totsy
 * @package     Totsy_Adminhtml_Block_Sales_Order
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Adminhtml_Block_Sales_Order_View
    extends Mage_Adminhtml_Block_Sales_Order_View
{
    public function __construct()
    {
        parent::__construct();

        if ('pending' == $this->getOrder()->getStatus()) {
            $confirm = "Are you sure you want to send this order for fulfillment?";
            $url     = $this->getUrl(
                '*/sales_order_edit/submitFulfillment',
                array('id' => $this->getOrderId())
            );
            $this->_addButton('order_submit_fulfillment', array(
                'label'     => Mage::helper('sales')->__('Submit for Fulfillment'),
                'onclick'   => "confirmSetLocation('$confirm', '$url')",
            ));
        }
    }
}
