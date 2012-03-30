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
class Harapartners_Service_Block_Rewrite_Checkout_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
	
	protected function _toHtml ()
	{
		$additional = '';
		
		if(!Mage::getSingleton('speedtax/session')->getAddressIsValidForSpeedTax()){
			$resolvedAddress = Mage::getSingleton('speedtax/session')->getResolvedAddressForSpeedTax();
			$addressString = $resolvedAddress->address.", ".$resolvedAddress->city.", ".$resolvedAddress->state." ".$resolvedAddress->zip;
			//Mage::getSingleton('core/session')->addNotice("Your address doen NOT match our record, The resolved address is ".$addressString);
			//$additional = $this->getMessagesBlock()->getGroupedHtml();
			//$additional = '<ul class="messages"><li class="notice-msg"><ul><li><span>Your address could not be verified. We found the following match ' . $addressString . ' Please correct your address if necessary. </span></li></ul></li></ul>';
		}
		return $additional . parent::_toHtml();
	}
	
}