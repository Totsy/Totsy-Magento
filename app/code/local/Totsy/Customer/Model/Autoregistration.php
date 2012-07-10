<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Customer_Model_Autoregistration extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('totsycustomer/autoregistration');
    }

    /**
     * Set the e-mail address on this autoregistration.
     *
     * @param string $email The new e-mail address.
     * @return Totsy_Customer_Model_Autoregistration
     */
    public function setEmail($email)
    {
        $this->setData('email', $email);
        $this->setData('token', hash('sha256', mt_rand()));

        return $this;
    }

    /**
     * Load an Autoregistration model, fetched by the token value.
     *
     * @param string $token
     *
     * @return Totsy_Customer_Model_Autoregistration
     */
    public function loadByToken($token)
    {
        $this->addData(
            $this->getResource()->loadByToken($token)
        );
        return $this;
    }

    /**
     * Create a new Customer record for this Autoregistration record.
     *
     * @return Mage_Customer_Model_Customer
     */
    public function createCustomer()
    {
        $customer = Mage::getModel('customer/customer');
        $customer->setEmail($this->getData('email'))
            ->setStoreId($this->getData('store_id'))
            ->setPassword($customer->generatePassword())
            ->save();

        return $customer;
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getId() && !$this->getData('created_at')) {
            $this->setData('created_at', now());
        }

        return $this;
    }
}
