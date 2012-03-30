<?php
class Harapartners_HpCheckout_Block_Login extends Harapartners_HpCheckout_Block_Abstract
{
    protected function _construct()
    {
        /*if (!$this->isCustomerLoggedIn()) {
            $this->getCheckout()->setStepData('login', array('label'=>Mage::helper('checkout')->__('Checkout Method'), 'allow'=>true));
        }*/
        parent::_construct();
    }

    public function getPostAction()
    {
        return Mage::getUrl('customer/account/loginPost', array('_secure'=>true));
    }

//    public function getMethod()
//    {
//        return $this->getQuote()->getCheckoutMethod();
//    }

    public function getUsername()
    {
        return Mage::getSingleton('customer/session')->getUsername(true);
    }
}
