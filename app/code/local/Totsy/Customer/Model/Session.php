<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Customer_Model_Session
    extends Mage_Customer_Model_Session
{
    // Harapartners, yang, add remember me time 30 * 24 * 3600, 1 month
    const REMEMBER_ME_PERIOD = 2592000;

    /**
     * The affiliate that a customer registered from.
     *
     * @var Harapartners_Affiliate_Model_Record
     */
    protected $_affiliate = null;

    /**
     * Adding an important step so that cache pages can still display messages.
     *
     * @param Mage_Core_Model_Message_Abstract $message
     *
     * @return Mage_Core_Model_Session_Abstract
     */
    public function addMessage(Mage_Core_Model_Message_Abstract $message)
    {
        if (Mage::app()->useCache('full_page')) {
            $cacheCookie = Mage::getSingleton('enterprise_pagecache/cookie');

            // mt_rand() range to 2^31, which should be sufficient
            // (global messages are transient and deleted after displayed)
            $cacheCookie->setObscure(
                Enterprise_PageCache_Model_Cookie::COOKIE_MESSAGE, md5(mt_rand())
            );
        }

        return parent::addMessage($message);
    }

    /**
     * Retrieve the affiliate that a customer registered from, or NULL if the
     * customer registered organically (not via an affiliate).
     *
     * @return Harapartners_Affiliate_Model_Record|null
     */
    public function getAffiliate()
    {
        if (!($this->_affiliate instanceof Harapartners_Affiliate_Model_Record)) {
            $this->_affiliate = Mage::getModel('affiliate/record');

            // always try to get the latest affiliate info,
            // setup caching separately if needed
            if ($this->getAffiliateId()) {
                $this->_affiliate->load($this->getAffiliateId());
            } elseif ($this->getAffiliateCode()) {
                $this->_affiliate->loadByAffiliateCode($this->getAffiliateCode());
            } else {
                // load from customer tracking record
                $customer = $this->getCustomer();
                if ($customer && $customer->getId()) {
                    $tracking = Mage::getModel('customertracking/record')
                        ->loadByCustomerEmail($customer->getEmail());
                    if ($tracking && $tracking->getId()) {
                        $this->_affiliate->load($tracking->getAffiliateId());
                    }
                }
            }
        }

        return $this->_affiliate;
    }

    /**
     * Set the affiliate that a customer registered from.
     *
     * @param Harapartners_Affiliate_Model_Record $affiliate
     *
     * @return Totsy_Customer_Model_Session
     */
    public function setAffiliate(Harapartners_Affiliate_Model_Record $affiliate)
    {
        $this->_affiliate = $affiliate;

        // save affiliate ID and code, for future retrieval of the affiliate object
        if ($this->_affiliate && $this->_affiliate->getId()) {
            $this->setAffiliateId($this->_affiliate->getId());
            $this->setAffiliateCode($this->_affiliate->getCode());
        }

        return $this;
    }

    public function login($username, $password)
    {
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

        if ($customer->authenticate($username, $password)) {
            $this->setCustomerAsLoggedIn($customer);
            $this->renewSession();

            // Harapartners, yang, START
            // Add remember me cookie time
            if (Mage::app()->getRequest()->getParam('rememberme')) {
                $this->getCookie()->set(
                    'remember_me',
                    'Remember Me',
                    self::REMEMBER_ME_PERIOD
                );
            }

            $model = Mage::getModel('customertracking/record')
                ->loadByCustomerEmail($customer->getEmail());
            if ($model->getId()) {
                $this->setAffiliateId($model->getAffiliateId());
                if ($model->getSubAffiliateCode()) {
                    $this->setSubAffiliateCode($model->getSubAffiliateCode());
                }
                if ($model->getRegistrationParam()) {
                    $this->setRegistrationParam($model->getRegistrationParam());
                }
            }

            return true;
        }

        return false;
    }

    public function setCustomerAsLoggedIn($customer)
    {
        $this->setCustomer($customer);
        $this->setData('CUSTOMER_LAST_VALIDATION_TIME', now());
        Mage::dispatchEvent('customer_login', array('customer'=>$customer));

        return $this;
    }

    /**
     * Harapartners, Jun
     * When name info is missing, auto populate name info from default billing
     * address.
     *
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        if ($this->_customer instanceof Mage_Customer_Model_Customer) {
            return $this->_customer;
        }

        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
        if ($this->getId()) {
            $customer->load($this->getId());
        }

        if (!$customer->getFirstname() || !$customer->getLastname()) {
            if ($defaultBillingAddress = $customer->getDefaultBillingAddress()) {
                $customer->setFirstname($defaultBillingAddress->getFirstname());
                $customer->setLastname($defaultBillingAddress->getLastname());
            }
        }

        $this->setCustomer($customer);
        return $this->_customer;
    }
}
