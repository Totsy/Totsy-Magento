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
}
