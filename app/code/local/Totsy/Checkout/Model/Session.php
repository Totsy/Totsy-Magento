<?php
/**
 * @category    Totsy
 * @package     Totsy_Checkout_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Checkout_Model_Session extends Mage_Checkout_Model_Session
{
    public function getQuoteItemExpireTime()
    {
        return Mage::getStoreConfig("config/rushcheckout_timer/limit_timer");
    }

    public function getQuote()
    {
        parent::getQuote();

        //Only check once for expiration, for non-empty quote
        if (!Mage::registry('has_expire_cart_by_rushcheckout')) {
            Mage::unregister('has_expire_cart_by_rushcheckout');
            Mage::register('has_expire_cart_by_rushcheckout', true);

            $now       = Mage::getModel('core/date')->timestamp();
            $countdown = $this->getCountDownTimer();
            $timeout   = $this->getQuoteItemExpireTime();

            if (count($this->_quote->getAllItems()) && $now - $countdown > $timeout) {
                foreach ($this->_quote->getAllItems() as $item) {
                    $item->isDeleted(true);
                    $item->delete();
                }

                $this->loadCustomerQuote();
            }
        }

        return $this->_quote;
    }
}
