<?php
class Harapartners_HpCheckout_Block_Review extends Harapartners_HpCheckout_Block_Abstract
{
    protected function _construct()
    {
        /*$this->getCheckout()->setStepData('review', array(
            'label'     => Mage::helper('checkout')->__('Order Review'),
            'is_show'   => $this->isShow()
        ));*/
        parent::_construct();

        //$this->getQuote()->collectTotals()->save();
    }
}
