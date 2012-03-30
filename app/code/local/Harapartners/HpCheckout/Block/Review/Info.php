<?php
class Harapartners_HpCheckout_Block_Review_Info extends Mage_Sales_Block_Items_Abstract
{
    public function getItems()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
    }

    public function getTotals()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getTotals();
    }
}
