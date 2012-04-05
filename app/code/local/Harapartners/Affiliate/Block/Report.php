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
			$reportHtml.= '<table>
							<tr>
							<th>Affiliate</th>
							<th>From</th>
							<th>To</th>								
							<th>Total Registrations</th>
							<th>Total Bounces</th>
							</tr>
							<tr>
							<td>'.$affiliate->getAffiliateCode().'</td>
							<td>'.$resultFilter->getFrom().'</td>
							<td>'.$resultFilter->getTo().'</td>					
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
								<td>'.$resultFilter->getFrom().'</td>
								<td>'.$resultFilter->getTo().'</td>					
								<td>'.$totalRegistrations.'</td>
								<td>'.$totalBounces.'</td>
								</tr>';	
				}
					$reportHtml.= '<tr>
					<td>grand total</td>
					<td>'.$resultFilter->getFrom().'</td>
					<td>'.$resultFilter->getTo().'</td>					
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
				//$customer = Mage::getModel('customer/customer')->loadByEmail($record->getCustomerEmail());
				$orderCollection = Mage::getModel('sales/order')->getCollection()
																	->addFieldToFilter('customer_id',$record->getCustomerId())
																	->addFieldToFilter('state','complete');	
				foreach ($orderCollection as $order) {
					$revenue+=$order->getGrandTotal();
				}
			}
			$grandRevenue = $revenue;
			$reportHtml.= '<table>
							<tr>
							<th>Affiliate</th>
							<th>From</th>
							<th>To</th>								
							<th>Total Revenue</th>
							</tr>
							<tr>
							<td>'.$affiliate->getAffiliateCode().'</td>
							<td>'.$resultFilter->getFrom().'</td>
							<td>'.$resultFilter->getTo().'</td>					
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
						//$customer = Mage::getModel('customer/customer')->loadByEmail($record->getCustomerEmail());
						$orderCollection = Mage::getModel('sales/order')->getCollection()
																			->addFieldToFilter('customer_id',$record->getCustomerId())
																			->addFieldToFilter('state','complete');	
						foreach ($orderCollection as $order) {
							$revenue+=$order->getGrandTotal();
						}
					}
					$grandRevenue+= $revenue;
					$reportHtml.= '<tr>
								<td>'.$affiliate->getAffiliateCode().'_'.$subAffiliate.'</td>
								<td>'.$resultFilter->getFrom().'</td>
								<td>'.$resultFilter->getTo().'</td>					
								<td>'.$revenue.'</td>
								</tr>';
				}
					$reportHtml.= '<tr>
					<td>grand total</td>
					<td>'.$resultFilter->getFrom().'</td>
					<td>'.$resultFilter->getTo().'</td>					
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
}