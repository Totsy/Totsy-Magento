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

abstract class Harapartners_SpeedTax_Model_Speedtax_Abstract extends Harapartners_SpeedTax_Model_Abstract {

    protected static $_hasError = false;
    protected $_request = null; //Jun, What is this?
    
    protected function _setOriginAddress($store = null) {
        $country = Mage::getStoreConfig('shipping/origin/country_id', $store);
        $zip = Mage::getStoreConfig('shipping/origin/postcode', $store);
        $regionId = Mage::getStoreConfig('shipping/origin/region_id', $store);
        $state = Mage::getModel('directory/region')->load($regionId)->getCode();
        $city = Mage::getStoreConfig('shipping/origin/city', $store);
        $street = Mage::getStoreConfig('shipping/origin/street', $store);
        $address = $this->_newAddress($street, '', $city, $state, $zip, $country);
        return $this->_request->setOriginAddress($address);
    }
    
    /**
     * Adds the shipping address to the request
     *
     * @param Address
     * @return bool
     */
    protected function _setDestinationAddress($address) {
        //$shippingAddress = $quote->getShippingAddress();
        $street = $address->getStreet();
        $street1 = isset($street[0]) ? $street[0] : null;
        $street2 = isset($street[1]) ? $street[1] : null;
        $city = $address->getCity();
        $zip = preg_replace('/[^0-9\-]*/', '', $address->getPostcode());
        $state = Mage::getModel('directory/region')->load($address->getRegionId())->getCode(); 
        $country = $address->getCountry();
         
        if(($city && $state) || $zip) {
            $address = $this->_newAddress($street1, $street2, $city, $state, $zip, $country);
            return $this->_request->setDestinationAddress($address);
        } else {
            return false;
        }
    }
    
    /**
     * Generic address maker
     *
     * @param string $line1 
     * @param string $line2 
     * @param string $city 
     * @param string $state 
     * @param string $zip 
     * @param string $country 
     * @return Address
     */
    protected function _newAddress($line1, $line2, $city, $state, $zip, $country='USA') {
        $address = new address();
        $address->setLine1($line1);
        $address->setLine2($line2);
        $address->setCity($city);
        $address->setRegion($state);
        $address->setPostalCode($zip);
        $address->setCountry($country);
        return $address;
    }
    
    /**
     * Test to see if the product carries its own numbers or is calculated based on parent or children
     *
     * @param Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item|mixed $item 
     * @return bool
     */
    public function isProductCalculated($item) {
        try {
            if($item->isChildrenCalculated() && !$item->getParentItem()) {
                return true;
            }
            if(!$item->isChildrenCalculated() && $item->getParentItem()) {
                return true;
            }
        } catch(Exception $e) { }
        return false;
    }
    
    /**
     * Adds a comment to order history. Method choosen based on Magento version.
     *
     * @param Mage_Sales_Model_Order
     * @param string
     * @return self
     */
    protected function _addStatusHistoryComment($order, $comment) {
        if(method_exists($order, 'addStatusHistoryComment')) {
            $order->addStatusHistoryComment($comment)->save();;
        } elseif(method_exists($order, 'addStatusToHistory')) {
            $order->addStatusToHistory($order->getStatus(), $comment, false)->save();;
        }
        return $this;
    }
}