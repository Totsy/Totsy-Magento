<?php
/**
 * TinyBrick Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the TinyBrick Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.delorumcommerce.com/license/commercial-extension
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@tinybrick.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future. 
 *
 * @category   TinyBrick
 * @package    TinyBrick_OrderEdit
 * @copyright  Copyright (c) 2010 TinyBrick Inc. LLC
 * @license    http://store.delorumcommerce.com/license/commercial-extension
 */
/**
 * Order payment information
 */
class TinyBrick_OrderEdit_Model_Order_Payment extends Mage_Payment_Model_Info
{
    protected $_eventPrefix = 'sales_order_payment';
    protected $_eventObject = 'payment';

    protected $_order;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('orderedit/order_payment');
    }

    /**
     * Declare quote model instance
     *
     * @param   Mage_Sales_Model_Quote $quote
     * @return  Mage_Sales_Model_Quote_Payment
     */
    public function setOrder(TinyBrick_OrderEdit_Model_Order $order)
    {
        $this->_order = $order;
        $this->setOrderId($order->getId());
        return $this;
    }

    /**
     * Retrieve quote model instance
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Import data
     *
     * @param array $data
     * @throws Mage_Core_Exception
     * @return Mage_Sales_Model_Quote_Payment
     */
    public function importData(array $data)
    {
        $data = new Varien_Object($data);
        Mage::dispatchEvent(
            $this->_eventPrefix . '_import_data_before',
            array(
                $this->_eventObject=>$this,
                'input'=>$data,
            )
        );

        $this->setMethod($data->getMethod());

        $method = $this->getMethodInstance();

        if (!$method->isAvailable($this->getOrder())) {
            Mage::throwException(Mage::helper('sales')->__('Requested Payment Method is not available'));
        }

        $method->assignData($data);
        /*
        * validating the payment data
        */
        $method->validate();
        return $this;
    }

    /**
     * Prepare object for save
     *
     * @return Mage_Sales_Model_Quote_Payment
     */
    protected function _beforeSave()
    {
        try {
            $method = $this->getMethodInstance();
        } catch (Mage_Core_Exception $e) {
            return parent::_beforeSave();
        }
        $method->prepareSave();
        if ($this->getOrder()) {
            $this->setQuoteId($this->getOrder()->getId());
        }
        return parent::_beforeSave();
    }

    public function getCheckoutRedirectUrl()
    {
        $method = $this->getMethodInstance();

        return $method ? $method->getCheckoutRedirectUrl() : false;
    }

    public function getOrderPlaceRedirectUrl()
    {
        $method = $this->getMethodInstance();

        return $method ? $method->getOrderPlaceRedirectUrl() : false;
    }
}