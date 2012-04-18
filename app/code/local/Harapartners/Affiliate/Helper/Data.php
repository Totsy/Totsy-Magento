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
 
class Harapartners_Affiliate_Helper_Data extends Mage_Core_Helper_Abstract{
	
	const COOKIE_AFFILIATE = 'AFFILIATE';
	const COOKIE_IMAGE_KEYWORD = 'AFFILIATEIMAGESWITCH';
	const PAGE_NAME_AFTER_CUSTOMER_REGISTER_SUCCESS = 'after_customer_register_success';
	
	public function getGridTypeArray(){
		return array(
				Harapartners_Affiliate_Model_Record::TYPE_STANDARD => 'Standard',
				Harapartners_Affiliate_Model_Record::TYPE_SUPER => 'Super'
		);
	}
	
	public function getFormTypeArray(){
    	return array(
       			array('label' => 'Standard', 'value' => Harapartners_Affiliate_Model_Record::TYPE_STANDARD),
       			array('label' => 'Super', 'value' => Harapartners_Affiliate_Model_Record::TYPE_SUPER)
       	);
	}
	
	public function getGridStatusArray(){
		return array(
				Harapartners_Affiliate_Model_Record::STATUS_ENABLED => 'Enabled',
				Harapartners_Affiliate_Model_Record::STATUS_DISABLED => 'Disabled'
		);
	}
	
	public function getFormStatusArray(){
		return array(
       			array('label' => 'Enabled', 'value' => Harapartners_Affiliate_Model_Record::STATUS_ENABLED),
       			array('label' => 'Disabled', 'value' => Harapartners_Affiliate_Model_Record::STATUS_DISABLED)
       	);
	}
	
	//Note page name must be  strtolower(Mage::app()->getFrontController()->getAction()->getFullActionName());
	public function getFormTrackingPageCodeArray(){
		return array(
		    		// ----- Atomic pages, determined by module/controller/action ----- //
		    		'customer_account_create' => 'Customer Account Create',
		    		'customer_account_login' => 'Customer Account Login', 
	       			'categoryevent_index_index' => 'Event Index (Home)', 
	       			'catalog_category_view' => 'Event Detail (Category)',
	       			'catalog_product_view' => 'Product Detail', 
	       			'hpcheckout_checkout_success' => 'Order Confirmation', //dependent on the checkout module
	       			
	       			// ----- Dependent on business logic, require cookie/session data ----- //
	       			self::PAGE_NAME_AFTER_CUSTOMER_REGISTER_SUCCESS => 'After Customer Register Success'

	       	);
//		    return array(
//		    		// ----- Atomic pages, determined by module/controller/action ----- //
//		    		array('label' => 'Customer Account Create', 'value' => 'customer_account_create'),
//		    		array('label' => 'Customer Account Login', 'value' => 'customer_account_login'),
//	       			array('label' => 'Event Index (Home)', 'value' => 'event_index_index'),
//	       			array('label' => 'Event Detail (Category)', 'value' => 'catalog_category_view'),
//	       			array('label' => 'Product Detail', 'value' => 'catalog_product_view'),
//	       			array('label' => 'Order Confirmation', 'value' => 'hpcheckout_checkout_success'), //dependent on the checkout module
//	       			
//	       			// ----- Dependent on business logic, require cookie/session data ----- //
//	       			array('label' => 'After Customer Register Success', 'value' => self::PAGE_NAME_AFTER_CUSTOMER_REGISTER_SUCCESS),
//
//	       	);
	}
	
	//Harapartners, yang: get cookie name for landing page's image keyword
	public function getKeywordCookieName() {
		return self::COOKIE_IMAGE_KEYWORD;
	}
	
}