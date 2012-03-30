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

class Inchoo_Api_Model_Customer_Address_Api extends Mage_Customer_Model_Address_Api
{
	public function info($addressId)
	{
		$address = Mage::getModel('customer/address')->load($addressId);

        if (!$address->getId()) {
            $this->_fault('not_exists');
        }
        
        $result = array(
        	//'type'		=>	'',
        	'firstname'	=>	$address->getFirstname(),
        	'lastname'	=>	$address->getLastname(),
        	'address'	=>	array($address->getStreet(1), $address->getStreet(2), $address->getStreet(3)),
        	'region'	=>	$address->getRegion(),
        	'country'	=>	$address->getCountry(),
        	'zip'		=>	$address->getPostcode(),
        );
        
        return $result;
	}
	
	public function update($addressId, $addressData)
	{	
		$address = Mage::getModel('customer/address')->load($addressId);
		
        if (!$address->getId()) {
            $this->_fault('not_exists');
        }
		foreach ($this->getAllowedAttributes($address) as $attributeCode=>$attribute) {
            if (isset($addressData[$attributeCode])) {
                $address->setData($attributeCode, $addressData[$attributeCode]);
            }
        }
        $valid = $address->validate();
        if (is_array($valid)) {
            $this->_fault('data_invalid', implode("\n", $valid));
        }

        try {
            $address->save();
        } catch (Mage_Core_Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        return true;
	}
}