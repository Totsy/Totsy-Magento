<?php
class Harapartners_HpCheckout_Block_Billing extends Harapartners_HpCheckout_Block_Abstract
{
    protected $_address;

    protected function _construct()
    {
        /*$this->getCheckout()->setStepData('billing', array(
            'label'     => Mage::helper('checkout')->__('Billing Information'),
            'is_show'   => $this->isShow()
        ));

        if ($this->isCustomerLoggedIn()) {
            $this->getCheckout()->setStepData('billing', 'allow', true);
        }*/
        parent::_construct();
    }

//    public function isUseBillingAddressForShipping()
//    {
//        if (($this->getQuote()->getIsVirtual())
//            || !$this->getQuote()->getShippingAddress()->getSameAsBilling()) {
//            return false;
//        }
//        return true;
//    }

    public function getCountries()
    {
        return Mage::getResourceModel('directory/country_collection')->loadByStore();
    }

//    public function getMethod()
//    {
//        return $this->getQuote()->getCheckoutMethod();
//    }
//
    public function getAddress()
    {
        if (is_null($this->_address)) {
//            if ($this->isCustomerLoggedIn()) {
                $this->_address = $this->getQuote()->getBillingAddress();
//            } else {
//                $this->_address = Mage::getModel('sales/quote_address');
//            }
        }

        return $this->_address;
    }

    public function getFirstname()
    {
        $firstname = $this->getAddress()->getFirstname();
        if (empty($firstname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getFirstname();
        }
        return $firstname;
    }

    public function getLastname()
    {
        $lastname = $this->getAddress()->getLastname();
        if (empty($lastname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getLastname();
        }
        return $lastname;
    }

    public function canShip()
    {
        return !$this->getQuote()->isVirtual();
    }

//    public function getSaveUrl()
//    {
//    }
}
