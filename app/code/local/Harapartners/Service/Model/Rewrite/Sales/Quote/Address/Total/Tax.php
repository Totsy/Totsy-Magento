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
class Harapartners_Service_Model_Rewrite_Sales_Quote_Address_Total_Tax extends Mage_Sales_Model_Quote_Address_Total_Abstract {
    
    protected $_address = null;
    
    /**
     * Class constructor
     */
    public function __construct() {
        Mage::helper ( 'speedtax' )->loadSpeedTaxLibrary ();
        $this->setCode ( 'tax' );
    }
    
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        $this->_setAddress ( $address );
        parent::collect ( $address );
        
        $store = $address->getQuote ()->getStore ();
        $customer = $address->getQuote ()->getCustomer ();
        
        $address->setTotalAmount ( $this->getCode (), 0 );
        $address->setBaseTotalAmount ( $this->getCode (), 0 );
        
        $address->setTaxAmount ( 0 );
        $address->setBaseTaxAmount ( 0 );
        $address->setShippingTaxAmount ( 0 );
        $address->setBaseShippingTaxAmount ( 0 );
        
        $address->setTaxAmount ( 0 );
        $address->setBaseTaxAmount ( 0 );
        $address->setShippingTaxAmount ( 0 );
        $address->setBaseShippingTaxAmount ( 0 );
        
        /****** make invoice ******/
        try {
            $calculator = Mage::getModel ( 'speedtax/speedtax_calculate' );
            if (!!$calculator->queryQuoteAddress($address)) {
                //Line item amount and shipping tax amount are set within the query
                $amount = $calculator->getTotalTax ();
                $this->_addAmount ( $amount );
                $this->_addBaseAmount ( $amount );
            }
        } catch( Exception $e ) {
            //Tax collecting is very important, bubble exceptions up
            throw new $e;
        }
        
        return $this;
    }
    
    protected function _setAddress(Mage_Sales_Model_Quote_Address $address) {
        $this->_address = $address;
        return $this;
    }
    
    protected function _getAddress() {
        if ($this->_address === null) {
            Mage::throwException ( Mage::helper ( 'sales' )->__ ( 'Address model is not defined' ) );
        }
        return $this->_address;
    }
    
    public function getItemRate($item) {
        if ($this->isProductCalculated ( $item )) {
            return 0;
        } else {
            $cacheKey = $this->_getRates ( $item );
            return array_key_exists ( $cacheKey, $this->_rates ) ? $this->_rates [$cacheKey] ['rate'] : 0;
        }
    }
    
    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $address->addTotal ( array (
                'code' => $this->getCode (), 
                'title' => Mage::helper ( 'tax' )->__ ( 'Tax' ), 
                'value' => $address->getTaxAmount (), 
                'area' => null ) );
        return $this;
    }
}
