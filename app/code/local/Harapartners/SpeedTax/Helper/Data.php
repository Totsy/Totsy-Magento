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
class Harapartners_SpeedTax_Helper_Data extends Mage_Core_Helper_Abstract{
    
    public function isAddressValidationOn($address, $storeId) {
        /*if(!$this->isAddressActionable($address, $storeId)) {
            return false;
        }*/
        return Mage::getStoreConfig('speedtax/speedtax/validate_address', $storeId);
    }
    
    public function getSpeedTaxLibraryDirectory(){
        return dirname(dirname(__FILE__)).DS.'lib'.DS;
    }
    
    public function loadSpeedTaxLibrary(){
        include_once $this->getSpeedTaxLibraryDirectory().'SpeedTaxApi.inc';
        include_once $this->getSpeedTaxLibraryDirectory().'SpeedTaxUtil.inc';
    }
}