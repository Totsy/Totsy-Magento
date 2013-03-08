<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Service_Model_Rewrite_Sales_Quote_Payment extends Mage_Sales_Model_Quote_Payment {
    
    public function importData(array $data, $shouldCollectTotal = true, $withValidate = true ) {
        $data = new Varien_Object($data);
        Mage::dispatchEvent(
            $this->_eventPrefix . '_import_data_before',
            array(
                $this->_eventObject=>$this,
                'input'=>$data,
            )
        );

        $this->setMethod($data->getMethod());
        $method = $this->getMethodInstance();

        /**
         * Payment avalability related with quote totals.
         * We have recollect quote totals before checking
         */
        $this->getQuote()->collectTotals();

        if (!$method->isAvailable($this->getQuote())) {
            Mage::throwException(Mage::helper('sales')->__('The requested Payment Method is not available.'));
        }
        if($data->getData('cybersource_subid')) {
            $data->setData('cybersource_subid', $this->_decryptSubscriptionId($data->getData('cybersource_subid')));
            $this->setData('cybersource_subid', $data->getData('cybersource_subid'));
            $method->setData('cybersource_subid', $data->getData('cybersource_subid'));
        }

        $method->assignData($data);
        
        $this->getQuote()->setData('saved_by_customer', $data[ 'saved_by_customer' ]);
        if($withValidate) {
            /*
            * validating the payment data
            */
            $method->validate();
        }
        return $this;
    }

    protected function _decryptSubscriptionId($subId){
        try{
            $testSubId = Mage::getModel('core/encryption')->decrypt(base64_decode($subId));
            if(is_numeric($testSubId)){
                $subId = $testSubId;
            }
        }catch (Exception $e){
        }
        return $subId;
    }
}