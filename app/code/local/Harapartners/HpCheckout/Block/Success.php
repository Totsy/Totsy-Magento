<?php
class Harapartners_HpCheckout_Block_Success extends Mage_Core_Block_Template
{
    public function isOrderVisible()
    {
        return (bool)$this->_getData('is_order_visible');
    }

    protected function _beforeToHtml()
    {
        $this->_prepareLastOrder();
        return parent::_beforeToHtml();
    }

    public function hasMultipleOrders() {
        $ids = Mage::getSingleton('core/session')->getOrderIds();
//        Zend_Debug::dump(Mage::getSingleton('core/session')->getOrderIds());
        if ($ids && is_array($ids)) {
            return TRUE;
        }
        else return FALSE;
    }

    public function getTheOrderIds() {
        if(!$this->hasOrderIds()) {
            $this->setOrderIds(Mage::getSingleton('core/session')->getOrderIds(TRUE));
            return $this->getOrderIds();
        }
        else return $this->getOrderIds();
    }

    public function getViewOrderUrl($orderId)
    {
        return $this->getUrl('sales/order/view/', array('order_id' => $orderId, '_secure' => true));
    }

    protected function _prepareLastOrder()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        if ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getId()) {
                $isVisible = !in_array($order->getState(),
                    Mage::getSingleton('sales/order_config')->getInvisibleOnFrontStates());
                $this->addData(array(
                    'is_order_visible' => $isVisible,
                    'view_order_url' => $this->getUrl('sales/order/view/', array('order_id' => $orderId)),
                    'print_url' => $this->getUrl('sales/order/print', array('order_id'=> $orderId)),
                    'can_print_order' => $isVisible,
                    'can_view_order'  => Mage::getSingleton('customer/session')->isLoggedIn() && $isVisible,
                    'order_id'  => $order->getIncrementId(),
                ));
            }
        }
    }
}
