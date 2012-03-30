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
		$customerSession = Mage::getSingleton('customer/session');
		$customer = $customerSession->getCustomer();
		if(!!$customer && !!$customer->getEmail() ){
			$customerTrackingRecord = Mage::getModel('customertracking/record')->loadByCustomerEmail($customer->getEmail());
			if(!!$customerTrackingRecord){
				if(!!$customerTrackingRecord->getAffiliateCode()){
					$affiliate = Mage::getModel('affiliate/record')->loadByAffiliateCode($customerTrackingRecord->getAffiliateCode());
				}elseif(!!$customerTrackingRecord->getAffiliateId()){
					$affiliate = Mage::getModel('affiliate/record')->load($customerTrackingRecord->getAffiliateId());
				} 
				if(!!Mage::registry('isLoginPage')){
					$currentPage = 'login';
				}
			}
		}elseif(!!$affiliateCode = Mage::getSingleton('customer/session')->getAffiliateCode()){
			$affiliate = Mage::getModel('affiliate/record')->loadByAffiliateCode($affiliateCode);
			if(!!Mage::registry('isLoginPage')){
				$currentPage = 'after_reg';
			}else{
				$currentPage = 'landing';
			}
		}
		if(!!$affiliate && !!$affiliate->getTrackingCode()){
				$trackingCode = json_decode($affiliate->getTrackingCode(),true);
				if(isset($trackingCode['pixels']) && !!$trackingCode['pixels']){
					foreach ($trackingCode['pixels'] as $pixel) {
						if(isset($pixel['enable']) && $pixel['enable']){
							if(!$currentPage){
								$currentPage = 'pageCanBeIgnored';	
							}						
							///page name matching
							foreach ($pixel['page'] as $page) {
								if($page == $currentPage){
									$pixelHtml.= $pixel['pixel'];
									break;
								}
							}								
						}
					}					
				}					
			}				
		return $pixelHtml;
	}
}
