<?php
/**
 * Created by JetBrains PhpStorm.
 * User: advocat
 * Date: 25.02.13
 * Time: 21:33
 * To change this template use File | Settings | File Templates.
 */
class Oro_Sales_Block_Order_Info_Button extends Mage_Core_Block_Template
{
    /**
     * Returns current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Check is show Edit Order Billing info link
     */
    public function isShowEditBilling()
    {
        $status = $this->getOrder()->getStatus();
        if ($status == Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED) {
            return true;
        }

        return false;
    }

    /**
     * Returns Edit Order Billing info URL
     *
     * @return string
     */
    public function getEditBillingUrl()
    {
        return $this->getUrl('sales/order_billing/edit', array(
            'order_id'  => $this->getOrder()->getId(),
        ));
    }
}
