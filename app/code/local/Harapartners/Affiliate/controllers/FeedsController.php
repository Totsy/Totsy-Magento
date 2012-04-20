<?php 
class Harapartners_Affiliate_FeedsController extends Mage_Core_Controller_Front_Action {
	
	const COUNTER_LIMIT = 2000;
		
	public function preDispatch() {
		header ("Content-Type:text/xml");
		parent::preDispatch ();
	}
	public function indexAction() {
		$request = $this->getRequest();
		$from = $request->getParam('from'); // format 20120401
		$to = $request->getParam('to');
		$type = $request->getParam('type');
		$token = $request->getParam('token');
		$affiliateCode = $request->getParam('affiliate_code');
		if(!$affiliateCode){
			$affiliateCode = 'keyade';
		}
		if(!!$request->getParam('period')){
			$period=3600*24*$request->getParam('period');
		} 
		if($token!='7cf7e9d58a213b2ebb401517d342475e'){
			echo "Invalid token";
			return;
		}
		if(!!$from && !!$to){
			$simpleXml = $this->_generateSimpleXml();		
			switch ($type) {
				case 'signups':
				$xml = $this->_createSignupsXml($simpleXml,$from,$to,$affiliateCode);
				break;
				
				case 'signupsByReferral':
				//Place holders !!!
				$xml = $this->_createSignupsByReferralXml($simpleXml,$from,$to,$affiliateCode);
				break;
				
				case 'sales':
				$xml = $this->_createSalesXml($simpleXml,$from,$to,$affiliateCode,$period);
				break;
				
				case 'referringSales':
				//Place holders !!!
				$xml = $this->_createReferringSalesXml($simpleXml,$from,$to,$affiliateCode,$period);
				break;
								
				default:				
				break;
			}						
			echo $xml->asXML ();
		}
	}
	
	protected function _generateSimpleXml(){
		$xmlStr = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<!DOCTYPE report PUBLIC "report" "https://dtool.keyade.com/dtd/conversions_v5.dtd">
<report></report>
XML;
		$xml = new SimpleXMLElement ($xmlStr);

		return $xml;
	}
	
	protected function _createSignupsXml($simpleXml,$from,$to,$affiliateCode){
		$recordCollection = Mage::getModel('customertracking/record')->getCollection()
																	->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
																	->addFieldToFilter('affiliate_code',$affiliateCode)
																	->addFieldToFilter('level', 0)
																	->setCurPage(1)
																	->setPageSize(200)
																	->load();													
		$counter = 0;
		foreach ($recordCollection as $record) {			
			$clickId = '';
			foreach (json_decode($record->getRegistrationParam(),true) as $index=>$value) {						
				if($index=="clickId"){
					$clickId = $value;
					$entryString = 'clickId='.$clickId.'  eventMerchantId='.$record->getCustomerId().'  count1="1"  time='.strtotime($record->getCreatedAt());
					$simpleXml->addChild ('entry', $entryString);	
					break;
				}
			}												
		}
		return $simpleXml;
	}
	
	protected function _createSignupsByReferralXml($simpleXml,$from,$to,$affiliateCode){
		//Place holders !!!
		$recordCollection = Mage::getModel('customertracking/record')->getCollection()
																	->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
																	->addFieldToFilter('affiliate_code',$affiliateCode)
																	->addFieldToFilter('level', 1)
																	->load();	
		foreach ($recordCollection as $record) {
		$clickId = '';
			foreach (json_decode($record->getRegistrationParam(),true) as $index=>$value) {						
				if($index=="clickid"){
					$clickId = $value;
					$entryString = 'clickId='.$clickId.'  eventMerchantId='.$record->getCustomerId().'  count1="1"  time='.strtotime($record->getCreatedAt());
					$simpleXml->addChild ('entry', $entryString);	
					break;
				}
			}												
		}
		return $simpleXml;
	}
	
	protected function _createSalesXml($simpleXml,$from,$to,$affiliateCode,$period){
		$recordCollection = Mage::getModel('customertracking/record')->getCollection()
																->addFieldToFilter('affiliate_code', $affiliateCode)
																->addFieldToFilter('level', 0)
																->load();	
		foreach ($recordCollection as $record) {
		// record may not have accurate customerId
			$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($record->getCustomerEmail());
			$clickId = '';
			foreach (json_decode($record->getRegistrationParam(),true) as $index=>$value) {						
				if($index=="clickid"){
					$clickId = $value;							
					break;
				}
			}
			$orderCollection = Mage::getModel('sales/order')->getCollection()
															->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
															->addFieldToFilter('customer_id',$customer->getId())
															->addFieldToFilter('state','complete')
															->load();	
			foreach ($orderCollection as $order) {
				if($period>0){
					$salesTime = strtotime($order->getCreatedAt());
					$registrationTime = strtotime($record->getCreatedAt());
					if($salesTime-$registrationTime<=$period){
						$entryString = 'clickId='.$clickId.' lifetimeId='.$record->getCustomerId().' eventMerchantId='.$order->getIncrementId().' count1="1" value1='.$order->getGrandTotal().' time='.$salesTime.' eventStatus="confirmed"';
						$simpleXml->addChild ('entry', $entryString);	
					}
				}else{
						$entryString = 'clickId='.$clickId.' lifetimeId='.$record->getCustomerId().' eventMerchantId='.$order->getIncrementId().' count1="1" value1='.$order->getGrandTotal().' time='.$salesTime.' eventStatus="confirmed"';
						$simpleXml->addChild ('entry', $entryString);
				}
			}			
		}			
		return $simpleXml;
	}
	
	protected function _createReferringSalesXml($simpleXml,$from,$to,$affiliateCode,$period){
		//Place holders !!!
		$recordCollection = Mage::getModel('customertracking/record')->getCollection()
																->addFieldToFilter('affiliate_code', $affiliateCode)
																->addFieldToFilter('level', 1)
																->load();	
		foreach ($recordCollection as $record) {
		// record may not have accurate customerId
			$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($record->getCustomerEmail());
			$clickId = '';
			foreach (json_decode($record->getRegistrationParam(),true) as $index=>$value) {						
				if($index=="clickid"){
					$clickId = $value;							
					break;
				}
			}
			$orderCollection = Mage::getModel('sales/order')->getCollection()
															->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
															->addFieldToFilter('customer_id',$customer->getId())
															->addFieldToFilter('state','complete')
															->load();	
			foreach ($orderCollection as $order) {
				if($period>0){
					$salesTime = strtotime($order->getCreatedAt());
					$registrationTime = strtotime($record->getCreatedAt());
					if($salesTime-$registrationTime<=$period){
						$entryString = 'clickId='.$clickId.' lifetimeId='.$record->getCustomerId().' eventMerchantId='.$order->getIncrementId().' count1="1" value1='.$order->getGrandTotal().' time='.$salesTime.' eventStatus="confirmed"';
						$simpleXml->addChild ('entry', $entryString);	
					}
				}else{
						$entryString = 'clickId='.$clickId.' lifetimeId='.$record->getCustomerId().' eventMerchantId='.$order->getIncrementId().' count1="1" value1='.$order->getGrandTotal().' time='.$salesTime.' eventStatus="confirmed"';
						$simpleXml->addChild ('entry', $entryString);
				}
			}			
		}			
		return $simpleXml;
	}	
	
}