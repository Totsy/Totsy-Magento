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
 * Oro Sales Order Billing Model
 *
 */
class Oro_Sales_Model_Order_Billing
{
    /**
     * Payment Instance
     *
     * @var Harapartners_Paymentfactory_Model_Tokenize
     */
    protected $_paymentInstance;

    /**
     * Returns Payment instance
     *
     * @return Harapartners_Paymentfactory_Model_Tokenize
     */
    protected function _getPaymentInstance()
    {
        if ($this->_paymentInstance === null) {
            $this->_paymentInstance = Mage::getModel('paymentfactory/tokenize');
        }

        return $this->_paymentInstance;
    }

    /**
     * Validates and import billing info to order
     *
     * @param Mage_Sales_Model_Order $order
     * @param array $data
     * @return bool|array
     */
    public function import(Mage_Sales_Model_Order $order, array $data)
    {
        // customer choose payment profile
        if (isset($data['payment'])) {
            return $this->_setPayment($order, $data);
        }
        return array(
            Mage::helper('oro_sales')->__('Invalid data.')
        );
    }

    /**
     * Replace Order billing address with customer address data
     *
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Customer_Model_Address $address
     */
    protected function _replaceBillingAddress(Mage_Sales_Model_Order $order, Mage_Customer_Model_Address $address)
    {
        $billing = $order->getBillingAddress();
        foreach ($address->getData() as $key => $value) {
            if (in_array($key, array('parent_id'))) {
                continue;
            }
            if ($billing->hasData($key)) {
                $billing->setDataUsingMethod($key, $value);
            }
        }
        $billing->setEmail($order->getCustomerEmail());
        $billing->setCustomerId($order->getCustomerId());
        $billing->setCustomerAddressId($address->getId());
        $billing->save();
    }

    /**
     * Saves Payment info
     *
     * @param Mage_Sales_Model_Order $order
     * @param array $data
     * @return array|bool
     */
    protected function _setPayment(Mage_Sales_Model_Order $order, array $data)
    {
        try {
            if(array_key_exists('cc_vaulted',$data['payment']) && $data['payment']['cc_vaulted']) {
                $profile = Mage::getModel('palorus/vault');
                $profile->load($data['payment']['cc_vaulted']);
                $address = Mage::getModel('customer/address')->load($profile->getAddressId());
            } else {
                $address = $this->_saveAddress($order, $data);
            }
            $billingId = $order->getBillingAddressId();
            $this->_replaceBillingAddress($order, $address);
            $billingId = $order->getBillingAddressId();
            $model = Mage::getModel('orderedit/edit_updater_type_payment');
            $mess = $model->edit($order,$data['payment']);

            if ($mess) {
                Mage::throwException($this->_getGeneralErrorMessage());
            }
            return true;
        } catch (Mage_Core_Exception $e) {
            return array(
                $e->getMessage()
            );
        }
    }

    /**
     * Checks and saves customer address info
     *
     * @param Mage_Sales_Model_Order $order
     * @param array $data
     * @return Mage_Customer_Model_Address
     */
    protected function _saveAddress(Mage_Sales_Model_Order $order, array $data)
    {
        /* @var $address Mage_Customer_Model_Address */
        $address = Mage::getModel('customer/address');

        if (!empty($data['billing_address_id'])) {
            $address->load($data['billing_address_id']);
            if (!$address->getId() || $address->getCustomerId() != $order->getCustomerId()) {
                Mage::throwException('Invalid data.');
            }
        } else {
            /* @var $addressForm Mage_Customer_Model_Form */
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')
                ->setEntityType('customer_address')
                ->setIsAjaxRequest(Mage::app()->getRequest()->isAjax());
            $addressForm->setEntity($address);

            $addressData    = $addressForm->extractData($addressForm->prepareRequest($data['billing']));
            $addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors !== true) {
                Mage::throwException(implode("\n", $addressErrors));
            }
            $addressForm->compactData($addressData);
            $address->setCustomerId($order->getCustomerId());
        }

        $addressErrors = $address->validate();
        if ($addressErrors !== true) {
            Mage::throwException(implode("\n", $addressErrors));
        }

        if (!$address->getId()) {
            $address->save();
        }

        return $address;
    }

    /**
     * Set Payment from saved CC Profile
     *
     * @param Mage_Sales_Model_Order $order
     * @param string $subId
     * @return bool
     */
    protected function _setPaymentByProfile(Mage_Sales_Model_Order $order, $subId)
    {
        /* @var $profile Harapartners_Paymentfactory_Model_Profile */
        $profile = Mage::getModel('paymentfactory/profile');
        $profile->load($subId);

        if (!$profile->getId() || $profile->getCustomerId() != $order->getCustomerId()) {
            return array(
                Mage::helper('oro_sales')->__('Invalid data.')
            );
        }

        $instance = $this->_getPaymentInstance();
        if (!$instance->checkProfile($profile)) {
            // delete profile ?
            $profile->setIsDefault(1);
            $profile->save();

            return array(
                $this->_getGeneralErrorMessage()
            );
        }

        /* @var $address Mage_Customer_Model_Address */
        $address = Mage::getModel('customer/address')->load($profile->getAddressId());
        $this->_replaceBillingAddress($order, $address);

        return $this->_replaceOrderPayment($order, $profile);
    }

    /**
     * Replace Order payment from profile
     *
     * @param Mage_Sales_Model_Order $order
     * @param Harapartners_Paymentfactory_Model_Profile $profile
     * @return bool
     */
    protected function _replaceOrderPayment(Mage_Sales_Model_Order $order,
        Harapartners_Paymentfactory_Model_Profile $profile)
    {
        $payment = $order->getPayment();
        if (!$payment instanceof Mage_Sales_Model_Order_Payment) {
            // restore payment
            /* @var $payment Totsy_Sales_Model_Order_Payment */
            $payment = Mage::getModel('sales/order_payment');
            $payment->setOrder($order);
            $payment->setParentId($order->getId());
            $payment->setAmountOrdered($order->getTotalDue());
            $payment->setBaseAmountOrdered($order->getBaseTotalDue());
            $payment->setShippingAmount($order->getShippingAmount());
            $payment->setBaseShippingAmount($order->getBaseShippingAmount());

            $order->getPaymentsCollection()->addItem($payment);
        }

        $payment->setMethod($this->_getPaymentInstance()->getCode());
        $payment->setCcLast4($profile->getLast4no());
        $payment->setCcType($profile->getCardType());
        $payment->setCcExpMonth($profile->getExpireMonth());
        $payment->setCcExpYear($profile->getExpireYear());
        $payment->setCybersourceSubid($profile->getSubscriptionId());
        $payment->setCybersourceToken(null);
        $payment->save();

        return true;
    }

    /**
     * Creates an invoice for order
     *
     * @param Mage_Sales_Model_Order $order
     * @throws Exception|Mage_Core_Exception
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function invoice(Mage_Sales_Model_Order $order)
    {
        try {
            /* @var $service Harapartners_Fulfillmentfactory_Model_Service_Dotcom */
            $service = Mage::getModel('fulfillmentfactory/service_dotcom');
            $order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
            $result  = $service->submitOrdersToFulfill(array($order), true);
            $status  = $order->getStatus();

            if ($status == Harapartners_Fulfillmentfactory_Helper_Data::ORDER_STATUS_PAYMENT_FAILED) {
                Mage::throwException(Mage::helper('oro_sales')->__('Cannot place payment information'));
            }

            /** @var $invoice Mage_Sales_Model_Order_Invoice */
            $invoice = $order->getInvoiceCollection()->getLastItem();
        } catch (Mage_Core_Exception $e) {
            throw $e;
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::throwException($this->_getGeneralErrorMessage());
            return false;
        }

        return $invoice;
    }

    /**
     * Returns General Error message
     *
     * @return string
     */
    protected function _getGeneralErrorMessage()
    {
        /* @var $urlModel Mage_Core_Model_Url */
        $urlModel = Mage::getModel('core/url');

        return Mage::helper('oro_sales')->__('Ooops, something went wrong. '
            . 'Please try again later or <a href="%s">contact customer service</a>.',
            $urlModel->getDirectUrl('app/ask')
        );
    }
}
