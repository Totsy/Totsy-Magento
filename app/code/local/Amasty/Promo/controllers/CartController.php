<?php
/**
* @copyright Amasty.
*/ 
class Amasty_Promo_CartController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    }

    public function restoreAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setAmpromoDeletedItems(null);
        $session->setAmpromoMessages(null);

        $this->_redirect('checkout/cart/index');
    }
}