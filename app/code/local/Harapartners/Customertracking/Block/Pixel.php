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

class Harapartners_Customertracking_Block_Pixel extends Mage_Core_Block_Template{
	
	public function _toHtml(){
		$pixelHtml = '';
		$affiliate = Mage::getSingleton('customer/session')->getAffiliate();
		if(!!$affiliate && !!$affiliate->getId()){
			try{
				$trackingCode = json_decode($affiliate->getTrackingCode(), true);
				//Page detection logic here!
				$currentPageTag = '';
				if(!empty($trackingCode[$currentPageTag])){
					$pixelHtml = $trackingCode[$currentPageTag];
				}
			}catch(Exception $e){
			}
		}
		return $pixelHtml;
	}
	
}
