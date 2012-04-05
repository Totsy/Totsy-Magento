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
class Harapartners_Service_Model_Rewrite_Sales_Quote_Address extends Mage_Sales_Model_Quote_Address
{
	protected $_speedtaxValidator = null;
	protected $_customerOrderCollection = null;
 
 	public function getCustomerOrderCollection(){
  
		if($this->_customerOrderCollection === null){
	   
	   		$orderCollection = Mage::getModel('sales/order')->getCollection();
	   		$customerId = $this->getCustomerId();
	   		$orderCollection->getSelect()
			   ->where('customer_id = ?', $customerId)
			   ->where('relation_parent_id is null') // orignal order
			   ->order('created_at')     //from 1st - 2nd
			   ->limit(2);
			    
			   $this->_customerOrderCollection = $orderCollection;
			   if(!$this->_customerOrderCollection){
			    	$this->_customerOrderCollection = Mage::getModel('sales/order')->getCollection() ;
			   }
		}
 	 	return $this->_customerOrderCollection;
 	}
	
	public function getSpeedTaxValidator() { 
		return $this->_speedtaxValidator;
	}
	
	public function setSpeedTaxValidator(Harapartners_SpeedTax_Model_SpeedTax_Address $object) { 
		$this->_speedtaxValidator = $object; 
		return $this;
	}
	
//	/**
//	 * Creates a hash key based on only address data for caching
//	 *
//	 * @return string
//	 */
//	public function getCacheHashKey() {
//		if(!$this->getData('cache_hash_key')) {
//			$this->setData('cache_hash_key', hash('md4', $this->format('text')));
//		}
//		return $this->getData('cache_hash_key');
//	}
//	
	/**
	 * Validates the address.  SpeedTax validation is invoked if the this is a ship-to address.
	 * Returns true on success and an array with an error on failure.
	 *
	 * @return true|array
	 */
	public function validate () {
		$result = parent::validate();
		
		//if base validation fails, don't bother with additional validation
		if ($result !== true) {  
			return $result;
		}
		
		//if ship-to address, do SpeedTax validation
		$data = Mage::app()->getRequest()->getPost('billing', array());
		$useForShipping = isset($data['use_for_shipping']) ? (int)$data['use_for_shipping'] : 0;
		
		if($this->getAddressType() == self::TYPE_SHIPPING || $this->getUseForShipping() /* <1.9 */ || $useForShipping /* >=1.9 */) {
			if(!$this->getSpeedTaxValidator()) {
				$validator = Mage::getModel('speedtax/speedtax_address')->setAddress($this);
				$this->setSpeedTaxValidator($validator);
			}
			return $this->getSpeedTaxValidator()->validate();
		}
		
		return $result;
	}	
    
}
