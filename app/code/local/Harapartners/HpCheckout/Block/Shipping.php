<?php
class Harapartners_HpCheckout_Block_Shipping extends Harapartners_HpCheckout_Block_Abstract
{
    protected $_address = null;

    protected function _construct()
    {
        /*$this->getCheckout()->setStepData('shipping', array(
            'label'     => Mage::helper('checkout')->__('Shipping Information'),
            'is_show'   => $this->isShow()
        ));*/

        parent::_construct();
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
                $this->_address = $this->getQuote()->getShippingAddress();
//            } else {
//                $this->_address = Mage::getModel('sales/quote_address');
//            }
        }

        return $this->_address;
    }
    
    /**
     * Return Customer Address First Name
     * If Sales Quote Address First Name is not defined - return Customer First Name
     *
     * @return string
     */
    public function getFirstname()
    {
        $firstname = $this->getAddress()->getFirstname();
        if (empty($firstname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getFirstname();
        }
        return $firstname;
    }

    /**
     * Return Customer Address Last Name
     * If Sales Quote Address Last Name is not defined - return Customer Last Name
     *
     * @return string
     */
    public function getLastname()
    {
        $lastname = $this->getAddress()->getLastname();
        if (empty($lastname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getLastname();
        }
        return $lastname;
    }

    public function isShow()
    {
        return !$this->getQuote()->isVirtual();
    }
}
