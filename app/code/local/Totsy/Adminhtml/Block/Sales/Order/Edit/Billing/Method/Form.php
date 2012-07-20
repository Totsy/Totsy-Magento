<?php
/**
 * @category    Totsy
 * @package     Totsy_Adminhtml_Block_Sales_Order_Create_Billing_Method_Form
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Adminhtml_Block_Sales_Order_Edit_Billing_Method_Form extends Mage_Adminhtml_Block_Sales_Order_Create_Billing_Method_Form
{
    /**
     * Retrieve availale payment methods
     *
     * @return array
     */
    public function getMethods()
    {
        $methods = $this->getData('methods');
        if (is_null($methods)) {
            $id = $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($id);
            //$quote = $this->getQuote();
            $store = $order ? $order->getStoreId() : null;
            $methods = $this->helper('payment')->getStoreMethods($store, $order);
            $total = $order->getBaseSubtotal();
            foreach ($methods as $key => $method) {
                if ($this->_canUseMethod($method)
                    && ($total != 0
                        || $method->getCode() == 'free'
                        || ($method->hasRecurringItems() && $method->canManageRecurringProfiles()))) {
                    $this->_assignMethod($method);
                } else {
                    unset($methods[$key]);
                }
            }
            $this->setData('methods', $methods);
        }
        return $methods;
    }
}