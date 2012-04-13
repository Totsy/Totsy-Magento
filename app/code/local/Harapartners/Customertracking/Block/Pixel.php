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
		$outputHtml = '';
		$affiliate = Mage::getSingleton('customer/session')->getAffiliate();
		if(!!$affiliate && !!$affiliate->getId()){
			try{
				$trackingCode = json_decode($affiliate->getTrackingCode(), true);
				$pixelHtml='';
				//Page detection
				$currentPageTag = strtolower(Mage::app()->getFrontController()->getAction()->getFullActionName());
				//fire GA event tracking
				$mapping = Mage::helper('affiliate')->getFormTrackingPageCodeArray();
				foreach ($mapping as $index=>$value) {
					if($index == $currentPageTag){						
						$affiliateEventName = $value;
					}
				}								
				$outputHtml.="<script type='text/javascript'> var _gaq = _gaq || [];
				_gaq.push(['_trackEvent', 'affiliate', '".$affiliateEventName."', '".$affiliate->getAffiliateCode()."']);</script>";						
				if(!empty($trackingCode[$currentPageTag])){
					$pixelHtml.= $trackingCode[$currentPageTag];
				}
				
				//Additional logic
				$cookie = Mage::app()->getCookie();
    			$key = Harapartners_Customertracking_Helper_Data::COOKIE_CUSTOMER_WELCOME;
    			if(!!$cookie->get($key)){
	    			if(!empty($trackingCode[Harapartners_Affiliate_Helper_Data::PAGE_NAME_AFTER_CUSTOMER_REGISTER_SUCCESS])){
						$pixelHtml.= $trackingCode[Harapartners_Affiliate_Helper_Data::PAGE_NAME_AFTER_CUSTOMER_REGISTER_SUCCESS];
					}
					//fire GA event tracking
					$outputHtml.="<script type='text/javascript'> var _gaq = _gaq || [];
					_gaq.push(['_trackEvent', 'affiliate', 'After Customer Register Success', '".$affiliate->getAffiliateCode()."']);</script>";
					
					$cookie->delete($key); //Note cookie still available till next page request
		    	}
				$varaiableArray = $this->_stringToArray($pixelHtml); 		
				foreach ($varaiableArray as $text) {
					if(is_array($text)){
						foreach ($text as $realText) {
							$outputHtml.= $realText;
						}
					}else{
						$outputHtml.= $text;	
					}				
				}				
			}catch(Exception $e){
				//handler
			}
		}		
		return $outputHtml;
	}
	protected function _stringToArray($string){
		if(!!$string){
			$array = explode('{{', $string);
			$i=count($array);
			for($j=1;$j<$i;$j++){
				$array[$j] = explode('}}',$array[$j]);
			}
			if($i>1){
				//$customer = Mage::getSingleton('customer/session')->getCustomer();
				$customer = Mage::getModel('customer/customer')->load(14);
				$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
				$order = Mage::getModel('sales/order')->load($orderId);
				for ($j=1;$j<$i;$j++){
					if(substr($array[$j][0],0,9)=='customer.'){
						$array[$j][0] = $customer->getData(substr($array[$j][0],9));
					}elseif (substr($array[$j][0],0,6)=='order.'){
						$array[$j][0] = $order->getData(substr($array[$j][0],6));
					}else{
						$array[$j][0] = '{{'.$array[$j][0].'}}';
					}
				}
			}
			return $array;
		}else{
			return array();
		}
	}
}
