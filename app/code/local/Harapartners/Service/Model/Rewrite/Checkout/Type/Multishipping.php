<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license [^]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 *
 */
class Harapartners_Service_Model_Rewrite_Checkout_Type_Multishipping extends Mage_Checkout_Model_Type_Abstract {
    
    //Haraparnters, this function is obsoleted
//    protected function _addressesValidate() {
//        $quote = $this->getQuote ();
//        if (! $quote->getIsMultiShipping ()) {
//            Mage::throwException ( $helper->__ ( 'Invalid checkout type.' ) );
//        }
//        $addresses = $quote->getAllShippingAddresses ();
//        foreach ( $addresses as $address ) {
//            $addressValidation = $address->validate ();
//        }
//    }
    
    protected function _validate() {
        $helper = Mage::helper ( 'checkout' );
        $quote = $this->getQuote ();
        if (! $quote->getIsMultiShipping ()) {
            Mage::throwException ( $helper->__ ( 'Invalid checkout type.' ) );
        }
        
        /** @var $paymentMethod Mage_Payment_Model_Method_Abstract */
        //Harapartners, Payment validataion is removed from here
//        $paymentMethod = $quote->getPayment()->getMethodInstance();
//        if (!empty($paymentMethod) && !$paymentMethod->isAvailable($quote)) {
//            Mage::throwException($helper->__('Please specify payment method.'));
//        }
        
        $addresses = $quote->getAllShippingAddresses ();
        foreach ( $addresses as $address ) {
            $addressValidation = $address->validate ();
            if ($addressValidation !== true) {
                Mage::throwException ( $helper->__ ( 'Please check shipping addresses information.' ) );
            }
            $method = $address->getShippingMethod ();
            $rate = $address->getShippingRateByCode ( $method );
            if (! $method || ! $rate) {
                Mage::throwException($helper->__('Please specify shipping methods for all addresses.'));
            }
        }
        $addressValidation = $quote->getBillingAddress ()->validate ();
        if ($addressValidation !== true) {
            Mage::throwException ( $helper->__ ( 'Please check billing address information.' ) );
        }
        return $this;
    }
    
}
