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

class Harapartners_Affiliate_Block_Report extends Mage_Adminhtml_Block_Template {
	public function getRegistrationHtml() {
		$resultFilter = Mage::registry('resultFilter');
		$reportHtml = '';
		if(!!$warningMessage = $resultFilter->getWarningMessage()){
			$reportHtml.= $warningMessage;
		}else{
			$affiliate = $resultFilter->getAffiliate();
			$from = $resultFilter->getFrom();
			$to = $resultFilter->getTo();
			$subAffiliateCode = $resultFilter->getSubAffiliateCode();
			$includeAllSubAffiliate = $resultFilter->getIncludeAllSubAffiliate();
			$recordCollection = Mage::getModel('customertracking/record')->getCollection()
															->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
															->addFieldToFilter('sub_affiliate_code', '')
															->addFieldToFilter('affiliate_code', $affiliate->getAffiliateCode());
			$bounceCollection = Mage::getModel('customertracking/record')->getCollection()
																			->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
																			->addFieldToFilter('affiliate_code', $affiliate->getAffiliateCode())
																			->addFieldToFilter('sub_affiliate_code', '')
																			->addFieldToFilter('status',array(3,4,5));															
			$totalRegistrations = $recordCollection->count();
			$totalBounces = $bounceCollection->count();
			$grandTotalRegistrations = $totalRegistrations;
			$grandTotalBounces = $totalBounces;
			$reportHtml.= $from.' -- '.$to.' :';	
			$reportHtml.= '<table>
							<tr>
							<th>Affiliate</th>							
							<th>Total Registrations</th>
							<th>Total Bounces</th>
							</tr>
							<tr>
							<td>'.$affiliate->getAffiliateCode().'</td>				
							<td>'.$totalRegistrations.'</td>
							<td>'.$totalBounces.'</td>
							</tr>';
			if(!!$includeAllSubAffiliate){	
				$subAffiliateArray = explode(',',$affiliate->getSubAffiliateCode());
				foreach ($subAffiliateArray as $subAffiliate) {
					$recordCollection = Mage::getModel('customertracking/record')->getCollection()
																			->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
																			->addFieldToFilter('affiliate_code', $affiliate->getAffiliateCode())
																			->addFieldToFilter('sub_affiliate_code', $subAffiliate);								
					$bounceCollection = Mage::getModel('customertracking/record')->getCollection()
																			->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
																			->addFieldToFilter('affiliate_code', $affiliate->getAffiliateCode())
																			->addFieldToFilter('sub_affiliate_code', $subAffiliate)
																			->addFieldToFilter('status',array(3,4,5));					
					$totalRegistrations = $recordCollection->count();
					$totalBounces = $bounceCollection->count();
					$grandTotalRegistrations+= $totalRegistrations;
					$grandTotalBounces+= $totalBounces;
					$reportHtml.= '<tr>
								<td>'.$affiliate->getAffiliateCode().'_'.$subAffiliate.'</td>				
								<td>'.$totalRegistrations.'</td>
								<td>'.$totalBounces.'</td>
								</tr>';	
				}
					$reportHtml.= '<tr>
					<td>grand total</td>				
					<td>'.$grandTotalRegistrations.'</td>
					<td>'.$grandTotalBounces.'</td>
					</tr>';																											
			}
			$reportHtml.= '</table> ';	
		}
		return  $reportHtml;		
	}
	
	public function getReVenueHtml(){
		$resultFilter = Mage::registry('resultFilter');
		$reportHtml = '';
		if(!!$warningMessage = $resultFilter->getWarningMessage()){
			$reportHtml.= $warningMessage;
		}else{
			$affiliate = $resultFilter->getAffiliate();
			$from = $resultFilter->getFrom();
			$to = $resultFilter->getTo();
			$subAffiliateCode = $resultFilter->getSubAffiliateCode();
			$includeAllSubAffiliate = $resultFilter->getIncludeAllSubAffiliate();
			$recordCollection = Mage::getModel('customertracking/record')->getCollection()
															->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
															->addFieldToFilter('sub_affiliate_code', '')
															->addFieldToFilter('affiliate_code', $affiliate->getAffiliateCode());	
			$revenue = 0;
			foreach ($recordCollection as $record) {
				// record may not have accurate customerId
				$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($record->getCustomerEmail());
				$orderCollection = Mage::getModel('sales/order')->getCollection()
																	->addFieldToFilter('customer_id',$customer->getId())
																	->addFieldToFilter('state','complete');	
				foreach ($orderCollection as $order) {
					$revenue+=$order->getGrandTotal();
				}
			}
			$grandRevenue = $revenue;
			$reportHtml.= $from.' -- '.$to.' :';	
			$reportHtml.= '<table>
							<tr>
							<th>Affiliate</th>								
							<th>Total Revenue</th>
							</tr>
							<tr>
							<td>'.$affiliate->getAffiliateCode().'</td>				
							<td>'.$revenue.'</td>
							</tr>';
			if(!!$includeAllSubAffiliate){	
				$subAffiliateArray = explode(',',$affiliate->getSubAffiliateCode());
				foreach ($subAffiliateArray as $subAffiliate) {
					$recordCollection = Mage::getModel('customertracking/record')->getCollection()
																			->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
																			->addFieldToFilter('affiliate_code', $affiliate->getAffiliateCode())
																			->addFieldToFilter('sub_affiliate_code', $subAffiliate);				
					$revenue = 0;
					foreach ($recordCollection as $record) {
						$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($record->getCustomerEmail());
						$orderCollection = Mage::getModel('sales/order')->getCollection()
																			->addFieldToFilter('customer_id',$customer->getId())
																			->addFieldToFilter('state','complete');	
						foreach ($orderCollection as $order) {
							$revenue+=$order->getGrandTotal();
						}
					}
					$grandRevenue+= $revenue;
					$reportHtml.= '<tr>
								<td>'.$affiliate->getAffiliateCode().'_'.$subAffiliate.'</td>				
								<td>'.$revenue.'</td>
								</tr>';
				}
					$reportHtml.= '<tr>
					<td>grand total</td>			
					<td>'.$grandRevenue.'</td>
					</tr>';																								
			}
			$reportHtml.= '</table> ';	
		}
		return  $reportHtml;		
	}	
	
	public function getBounceHtml(){
		$resultFilter = Mage::registry('resultFilter');
		$reportHtml = '';
		if(!!$warningMessage = $resultFilter->getWarningMessage()){
			$reportHtml.= $warningMessage;
		}else{
			$affiliate = $resultFilter->getAffiliate();
			$from = $resultFilter->getFrom();
			$to = $resultFilter->getTo();
			$subAffiliateCode = $resultFilter->getSubAffiliateCode();
			$includeAllSubAffiliate = $resultFilter->getIncludeAllSubAffiliate();
			$recordCollection = Mage::getModel('customertracking/record')->getCollection()
															->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
															->addFieldToFilter('sub_affiliate_code', '')
															->addFieldToFilter('affiliate_code', $affiliate->getAffiliateCode())
															->addFieldToFilter('status',array(3,4,5));	
			$reportHtml.= $from.' -- '.$to.' :';	
			$reportHtml.= '<table>
						<tr>
						<th>Affiliate</th>
						<th>Email</th>								
						<th>Registration Time</th>
						<th>Bounce Type</th>
						<th>Last Bounced</th>
						</tr>';
			foreach ($recordCollection as $record) {
				$status = $record->getStatus();
				if($status==3){
					$bounceType = 'softbounce';
				}elseif($status==4){
					$bounceType = 'hardbounce';
				}else{
					$bounceType = 'otherbounce';
				}
				$reportHtml.='<tr>
						<td>'.$affiliate->getAffiliateCode().'</td>
						<td>'.$record->getCustomerEmail().'</td>
						<td>'.$record->getCreatedAt().'</td>					
						<td>'.$bounceType.'</td>
						<td>'.$record->getUpdatedAt().'</td>
						</tr>';
			}
			if(!!$includeAllSubAffiliate){	
				$subAffiliateArray = explode(',',$affiliate->getSubAffiliateCode());
				foreach ($subAffiliateArray as $subAffiliate) {
					$recordCollection = Mage::getModel('customertracking/record')->getCollection()
																			->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
																			->addFieldToFilter('affiliate_code', $affiliate->getAffiliateCode())
																			->addFieldToFilter('sub_affiliate_code', $subAffiliate)
																			->addFieldToFilter('status',array(3,4,5));
					foreach ($recordCollection as $record) {
						$status = $record->getStatus();
						if($status==3){
							$bounceType = 'softbounce';
						}elseif($status==4){
							$bounceType = 'hardbounce';
						}else{
							$bounceType = 'otherbounce';
						}
						$reportHtml.='<tr>
								<td>'.$affiliate->getAffiliateCode().'_'.$subAffiliate.'</td>
								<td>'.$record->getCustomerEmail().'</td>
								<td>'.$record->getCreatedAt().'</td>					
								<td>'.$bounceType.'</td>
								<td>'.$record->getUpdatedAt().'</td>
								</tr>';
					}														
				}
			}	
			$reportHtml.= '</table> ';				
		}
		return $reportHtml;
	}
	
	public function getEffeticeHtml(){
		$resultFilter = Mage::registry('resultFilter');
		$reportHtml = '';
		if(!!$warningMessage = $resultFilter->getWarningMessage()){
			$reportHtml.= $warningMessage;
		}else{
			$affiliate = $resultFilter->getAffiliate();
			$from = $resultFilter->getFrom();
			$to = $resultFilter->getTo();
			$subAffiliateCode = $resultFilter->getSubAffiliateCode();
			$includeAllSubAffiliate = $resultFilter->getIncludeAllSubAffiliate();
			$recordCollection = Mage::getModel('customertracking/record')->getCollection()
															->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
															->addFieldToFilter('sub_affiliate_code', '')
															->addFieldToFilter('affiliate_code', $affiliate->getAffiliateCode());
			$never = array(0,0); $zero = array(0,0); $ten = array(0,0); $twenty = array(0,0); $thirty = array(0,0); $forty = array(0,0);
			$totalValuedCustomer = 0;
			foreach ($recordCollection as $record) {
				$loginCount = $record->getLoginCount();	
				$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($record->getCustomerEmail());
				$orderCollection = Mage::getModel('sales/order')->getCollection()
																->addFieldToFilter('customer_id',$customer->getId())
																->addFieldToFilter('state','complete');	
				$count = $orderCollection->count();
				if($count>0){
					$totalValuedCustomer+= 1;
				}		
				if($loginCount<=1){
					$never[0]+= 1;
					if($count>0){
						$never[1]+= 1;
					}					
				}elseif($loginCount>1 && $loginCount<10){
					$zero[0]+= 1;
					if($count>0){
						$zero[1]+= 1;
					}
				}elseif($loginCount>=10 && $loginCount<20){
					$ten[0]+=1;
					if($count>0){
						$ten[1]+= 1;
					}
				}elseif($loginCount>=20 && $loginCount<30){
					$twenty[0]+=1;
					if($count>0){
						$twenty[1]+= 1;
					}
				}elseif($loginCount>=30 && $loginCount<40){
					$thirty[0]+=1;
					if($count>0){
						$thirty[1]+= 1;
					}
				}else{
					$forty[0]+=1;
					if($count>0){
						$forty[1]+= 1;
					}
				}
			}													
			$reportHtml.= 'Number in parentheses show number of people made at least one purchase<br/>'.$from.' -- '.$to.' :';	
			$reportHtml.= '<table>
				<tr>
				<th>Affiliate</th>	
				<th>Total Registration</th>						
				<th>Never Login</th>
				<th>Login 0x</th>
				<th>Login 1x</th>
				<th>Login 2x</th>
				<th>Login 3x</th>
				<th>Login 4x or More</th>
				</tr>
				<tr>
				<td>'.$affiliate->getAffiliateCode().'</td>	
				<td>'.$recordCollection->count().' ('.$totalValuedCustomer.')'.'</td>			
				<td>'.$never[0].' ('.$never[1].')'.'</td>
				<td>'.$zero[0].' ('.$zero[1].')'.'</td>
				<td>'.$ten[0].' ('.$ten[1].')'.'</td>
				<td>'.$twenty[0].' ('.$twenty[1].')'.'</td>
				<td>'.$thirty[0].' ('.$thirty[1].')'.'</td>
				<td>'.$forty[0].' ('.$forty[1].')'.'</td>
				</tr>';
			if(!!$includeAllSubAffiliate){	
				$subAffiliateArray = explode(',',$affiliate->getSubAffiliateCode());
				foreach ($subAffiliateArray as $subAffiliate) {
					$recordCollection = Mage::getModel('customertracking/record')->getCollection()
																			->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
																			->addFieldToFilter('affiliate_code', $affiliate->getAffiliateCode())
																			->addFieldToFilter('sub_affiliate_code', $subAffiliate);
					$never = array(0,0); $zero = array(0,0); $ten = array(0,0); $twenty = array(0,0); $thirty = array(0,0); $forty = array(0,0);
					$totalValuedCustomer = 0;
					foreach ($recordCollection as $record) {
						$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($record->getCustomerEmail());
						$orderCollection = Mage::getModel('sales/order')->getCollection()
																->addFieldToFilter('customer_id',$customer->getId())
																->addFieldToFilter('state','complete');	
						$count = $orderCollection->count();
						$loginCount = $record->getLoginCount();						
						if($count>0){
							$totalValuedCustomer+= 1;
						}		
						if($loginCount<=1){
							$never[0]+= 1;
							if($count>0){
								$never[1]+= 1;
							}					
						}elseif($loginCount>1 && $loginCount<10){
							$zero[0]+= 1;
							if($count>0){
								$zero[1]+= 1;
							}
						}elseif($loginCount>=10 && $loginCount<20){
							$ten[0]+=1;
							if($count>0){
								$ten[1]+= 1;
							}
						}elseif($loginCount>=20 && $loginCount<30){
							$twenty[0]+=1;
							if($count>0){
								$twenty[1]+= 1;
							}
						}elseif($loginCount>=30 && $loginCount<40){
							$thirty[0]+=1;
							if($count>0){
								$thirty[1]+= 1;
							}
						}else{
							$forty[0]+=1;
							if($count>0){
								$forty[1]+= 1;
							}
						}
					}	
				$reportHtml.='<tr>
							<td>'.$affiliate->getAffiliateCode().'_'.$subAffiliate.'</td>
							<td>'.$recordCollection->count().' ('.$totalValuedCustomer.')'.'</td>			
							<td>'.$never[0].' ('.$never[1].')'.'</td>
							<td>'.$zero[0].' ('.$zero[1].')'.'</td>
							<td>'.$ten[0].' ('.$ten[1].')'.'</td>
							<td>'.$twenty[0].' ('.$twenty[1].')'.'</td>
							<td>'.$thirty[0].' ('.$thirty[1].')'.'</td>
							<td>'.$forty[0].' ('.$forty[1].')'.'</td>
							</tr>';								
				}
			}	
			$reportHtml.= '</table> ';	
		}
		return $reportHtml;
	}
	
}