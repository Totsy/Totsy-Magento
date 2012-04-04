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
class Harapartners_DropshipFactory_Helper_Data extends Mage_Core_Helper_Abstract{
	
	/**
	 * get product attribute's Id
	 *
	 * @return int attribute id
	 */
	public function getAttributeId($attributeLabel) {
		return Mage::getResourceModel('eav/entity_attribute')
										->getIdByCode('catalog_product',$attributeLabel);
	}
	
	/**
	 * get vendor attribute's Id
	 *
	 * @return int attribute id
	 */
	public function getVendorAttributeId() {
		
		return $this->getAttributeId('vendor_code');
	}
	
	/**
	 * get vendor_style attribute's Id
	 *
	 * @return int attribute id
	 */
	public function getVendorStyleAttributeId() {
		
		return $this->getAttributeId('vendor_style');
	}
	
	/**
	 * get fulfillment_type attribute's Id
	 *
	 * @return int attribute id
	 */
	public function getFulfillmentTypeAttributeId() {
		
		return $this->getAttributeId('fulfillment_type');
	}
	
	/**
	 * get Vendor List
	 * Notice: there is a bug with vendor_code because of attribute changes
	 * @return array
	 */
	public function getVendorList() {
		$vendorAttribute = Mage::getModel('catalog/entity_attribute')
								->load(Mage::getResourceModel('eav/entity_attribute')
										->getIdByCode('catalog_product','vendor_code')
								  );
								  
								  
		$options = $vendorAttribute->getSource()->getAllOptions();
		$vendorList = array();
		
		foreach($options as $option){
			if(!empty($option['value'])) {
				$vendorList[$option['value']] = $option['label'];
			}
		}
		
		return $vendorList;
    }
}