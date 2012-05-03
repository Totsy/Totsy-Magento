<?php
class Harapartners_HpCheckout_Block_Payment extends Harapartners_HpCheckout_Block_Abstract
{
    protected function _construct()
    {
        /*$this->getCheckout()->setStepData('payment', array(
            'label'     => $this->__('Payment Information'),
            'is_show'   => $this->isShow()
        ));*/
        parent::_construct();
    }

    public function getPaymentMethodFormHtml(Mage_Payment_Model_Method_Abstract $method)
    {
         return $this->getChildHtml('payment.method.' . $method->getCode());
    }
    
    public function getPaymentMethodsSelectHtml() {
        
    }
    
//    public function getQuoteBaseGrandTotal()
//    {
//        return (float)$this->getQuote()->getBaseGrandTotal();
//    }
}
