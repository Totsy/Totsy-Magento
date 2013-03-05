<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_Sales
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

/**
 * Edit Sales Order Billing Info
 */
class Oro_Sales_Order_BillingController extends Mage_Core_Controller_Front_Action
{
    /**
     * Checks customer authentication
     */
    public function preDispatch()
    {
        parent::preDispatch();

        /* @var $session Totsy_Customer_Model_Session */
        $session  = Mage::getSingleton('customer/session');
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!$session->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     * Initializes and returns order by request
     *
     * Returns false if order does not exists or cannot be edit
     * Registers order to register with key current_order
     *
     * @return bool|Crown_Club_Model_Sales_Order
     */
    protected function _initOrder()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if (!$orderId) {
            return false;
        }

        /* @var $order Crown_Club_Model_Sales_Order */
        $order   = Mage::getModel('sales/order')->load($orderId);
        if (!$order->getId()) {
            return false;
        }

        /* @var $session Totsy_Customer_Model_Session */
        $session  = Mage::getSingleton('customer/session');
        $customer = $session->getCustomer();
        if ($order->getCustomerId() != $customer->getId()) {
            return false;
        }

        if ($order->getStatus() != Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED) {
            return false;
        }

        Mage::register('current_order', $order);

        return $order;
    }

    /**
     * Edit Order Billing Info action
     */
    public function editAction()
    {
        $order = $this->_initOrder();
        if (!$order) {
            $this->norouteAction();

            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }

    /**
     * Saves order billing info and try to create an invoice
     */
    public function saveAction()
    {
        $order = $this->_initOrder();
        if (!$order) {
            $this->norouteAction();

            return;
        }

        $data     = $this->getRequest()->getParams();
        /* @var $session Totsy_Customer_Model_Session */
        $session  = Mage::getSingleton('catalog/session');
        /* @var $billing Oro_Sales_Model_Order_Billing */
        $billing  = Mage::getModel('oro_sales/order_billing');
        $result   = $billing->import($order, $data);
        if ($result !== true) {
            $session->addError(implode("\n", $result));
            $this->_redirect('*/*/edit', array('order_id' => $order->getId()));

            return;
        }

        $redirect = '*/*/edit';
        try {
            $invoice  = $billing->invoice($order);
            $session->addSuccess(Mage::helper('oro_sales')->__('Order was updated successfully - Invoice #%s',
                $invoice->getIncrementId()));
            $redirect = 'sales/order/view';
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addException($e, '');
        }

        $this->_redirect($redirect, array('order_id' => $order->getId()));
    }
}
