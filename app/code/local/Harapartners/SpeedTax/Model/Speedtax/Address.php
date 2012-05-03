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
class Harapartners_SpeedTax_Model_Speedtax_Address extends Harapartners_SpeedTax_Model_Abstract {
    protected $_mageAddress = null;
    
    protected $_requestAddress = array ();
    
    protected $_responseAddress = null;
    
    protected $_storeId = null;
    
    public function __construct() {
        Mage::helper('speedtax')->loadSpeedTaxLibrary();
        parent::__construct ();
    }
    
    /**
     * Saves any current addresses to session
     *
     */
    public function __destruct() {
        if (method_exists ( get_parent_class (), '__destruct' )) {
            parent::__destruct ();
        }
    }
    
    public function setAddress(Mage_Customer_Model_Address_Abstract $address) {
        $this->_storeId = $address->getQuote ()->getStore ()->getId ();
        $this->_mageAddress = $address;
        $this->_renderRequestAddress ();
        return $this;
    }
    
    protected function _renderRequestAddress() {
        if (!$this->_requestAddress) {
            $this->_requestAddress = new address();
        }
        $street = $this->_mageAddress->getStreet(1). " ";
        $street .= $this->_mageAddress->getStreet(2);
        $city = $this->_mageAddress->getCity();
        $state = $this->_mageAddress->getRegionCode();
        $zip = $this->_mageAddress->getPostcode();
        
        $this->_requestAddress->address1 = $street;
        $this->_requestAddress->address2 = $city . ", " . $state . " " . $zip ;//. ", " . $country;
        
        return $this;
    }
    
    protected function _convertResponseAddress() {
        $street = array (
                $this->_responseAddress->getLine1 (), 
                $this->_responseAddress->getLine2 () );
        $region = Mage::getModel ( 'directory/region' )->loadByCode ( $this->_responseAddress->getRegion (), $this->_mageAddress->getCountryId () );
        
        $this->_mageAddress->setStreet ( $street )->setCity ( $this->_responseAddress->getCity () )->setRegionId ( $region->getId () )->setPostcode ( $this->_responseAddress->getPostalCode () )->setCountryId ( $this->_responseAddress->getCountry () )->save ()->setAddressNormalized ( true );
        return $this;
    }
    
    public function validate() {
        if (! $this->_mageAddress) {
            throw new Harapartners_SpeedTax_Model_Speedtax_Address_Exception ( $this->__ ( 'An address must be set before validation.' ) );
        }
        
        //$config = Mage::getSingleton ( 'speedtax/config' )->init ( $this->_storeId );
        $isAddressValidationOn = Mage::helper ( 'speedtax' )->isAddressValidationOn ( $this->_mageAddress, $this->_storeId );
        //$isAddressNormalizationOn = Mage::helper('speedtax')->isAddressNormalizationOn($this->_mageAddress, $this->_storeId);
        //$isQuoteActionable = Mage::helper('speedtax')->isObjectActionable($this->_mageAddress->getQuote(), $this->_mageAddress);
        

        if (! $isAddressValidationOn) { //&& !$isAddressNormalizationOn && !$isQuoteActionable) 
            return true;
        }
        //address validation
        $stx = new SpeedTax();
        $result = $stx->ResolveAddress ( $this->_requestAddress );
        $this->_responseAddress = $result->ResolveAddressResult->resolvedAddress;
        
        switch ($result->ResolveAddressResult->resultType) {
            case  'FULL' :
                Mage::getSingleton('speedtax/session')->setAddressIsValidForSpeedTax(true);
                break;
            case 'FALLBACK' ||'STATE' || 'UNRESOLVED' :
                Mage::getSingleton('speedtax/session')->setAddressIsValidForSpeedTax(false);
                Mage::getSingleton('speedtax/session')->setResolvedAddressForSpeedTax($this->_responseAddress);
                $addressString = $this->_responseAddress->address.", ".$this->_responseAddress->city.", ".$this->_responseAddress->state." ".$this->_responseAddress->zip;
                //Mage::getSingleton('core/session')->addNotice("Your address could not be verified. We found the following match ' . $addressString . ' Please correct your address if necessary. ");
                break;
        }
        
        return true;
        
    /*switch ($result->ResolveAddressResult->resultType) {
            case 'STATE' :
                print "Address resolved at STATE level:\n";
                DisplayFullAddress ( $result->ResolveAddressResult->resolvedAddress, "" );
                DisplayJurisdictions ( $result->ResolveAddressResult->jurisdictions );
                break;
            case 'FALLBACK' :
                print "Address resolved at ZIP CODE level:\n";
                DisplayFullAddress ( $result->ResolveAddressResult->resolvedAddress, "" );
                DisplayJurisdictions ( $result->ResolveAddressResult->jurisdictions );
                break;
            case 'FULL' :
                print "Address fully resolved:\n";
                DisplayFullAddress ( $result->ResolveAddressResult->resolvedAddress, "" );
                DisplayJurisdictions ( $result->ResolveAddressResult->jurisdictions );
                break;
            case 'UNRESOLVED' :
                print "Address not resolved:\n";
                DisplayFullAddress ( $result->ResolveAddressResult->resolvedAddress, "" );
                DisplayJurisdictions ( $result->ResolveAddressResult->jurisdictions );
                break;
        }*/
    }
}
