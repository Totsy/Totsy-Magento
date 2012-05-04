<?php 
class Harapartners_HpCheckout_Block_Shippingmethod extends Harapartners_HpCheckout_Block_Abstract {
    protected $_rates;
    
    public function getShippingRates()
    {
        if (empty($this->_rates)) {
            $this->getAddress()->collectShippingRates()->save();
            $groups = $this->getAddress()->getGroupedAllShippingRates();
            $preparedGroups = $this->_prepareGroupedShippingRates( $groups );
            return $this->_rates = $preparedGroups;
        }

        return $this->_rates;
    }

    public function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }
        return $this->_address;
    }
    
    public function getAddressShippingMethod()
    {
        return $this->getAddress()->getShippingMethod();
    }
    
    public function getCarrierName($carrierCode)
    {
        if ($name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title')) {
            return $name;
        }
        return $carrierCode;
    }
    
    protected function _prepareGroupedShippingRates( $shippingRateGroups ) {
        $ret = array();
        foreach( $shippingRateGroups as $code => $rates ) {
            foreach( $rates as $rate ) {
                if( ! $rate->getErrorMessage() ) {
                    $ret[ $rate->getCode() ] = $this->getCarrierName( $code ) . " - " . $rate->getMethodTitle() . " - " . $this->getQuote()->getStore()->formatPrice( ( float ) $rate->getPrice() );
                }
            }
        }
        return $ret;
    }
}