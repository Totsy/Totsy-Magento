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
	
	public function getAffiliate(){
		$affiliate = null;
		$customerSession = Mage::getSingleton('customer/session');
		$customer = $customerSession->getCustomer();
		
		//if customer does not login, try to load affiliate code from session
		//previously affiliate router direct to /affiliate/register controller, save affiliate code (TODO: all info) to session
		//add affiliate info to session only upon register and login (can register be merged into login??)
		
		//up to here, if there is an affiliate association, it must be already in the session
		
		
		//get affilicate from session
		//if afflicate exists
		//		logic to determin which page I'm on right now, load the tracking pixel from afflicate
		//		this can be determined by Mage::app()->getRequest() or Mage::app()->getFrontController()
		//		some complex logic can be applied here
		
		//else do Nothing
		
		
		
		//customer exists (have id)
		if(!!$customer 
				&& !!$customer->getId()
			
		 //&& !!$customer->getEmail() 
		 ){
		 	//check if this customer has affiliate from customertracking/record, which is only an association
			$customerTrackingRecord = Mage::getModel('customertracking/record')->loadByCustomerEmail($customer->getEmail());
			if(!!$customerTrackingRecord
					&& !!$customerTrackingRecord->getId() 
			){
				//
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
		return $affiliate;
	}
	
}
