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
	
	public function getReportHtml(){  
		$resultFilter = Mage::registry('resultFilter');
		$reportHtml = '';
		if(!!$warningMessage = $resultFilter->getWarningMessage()){
			$reportHtml.= $warningMessage;
		}else{
			$affiliate = $resultFilter->getAffiliate();
			$from = $resultFilter->getFrom();
			$to = $resultFilter->getTo();
			$includeAllSubAffiliate = $resultFilter->getIncludeAllSubAffiliate();
			$subAffiliateCode = $resultFilter->getSubAffiliateCode();
			if(!!$includeAllSubAffiliate && !!$subAffiliateCode){
				$reportHtml.='Can not check "include all subaffiliate" and fill subaffiliate code at the same time';
			}else{
				if(!!$includeAllSubAffiliate){
					$recordCollection = Mage::getModel('customertracking/record')->getCollection()
																			->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
																			->addFieldToFilter('affiliate_code', $affiliate->getAffiliateCode());
					$bounceCollection = Mage::getModel('customertracking/record')->getCollection()
																			->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
																			->addFieldToFilter('affiliate_code', $affiliate->getAffiliateCode())
																			->addFieldToFilter('status',array(3,4,5));	
				}else{
					$recordCollection = Mage::getModel('customertracking/record')->getCollection()
																			->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
																			->addFieldToFilter('affiliate_code', $affiliate->getAffiliateCode())
																			->addFieldToFilter('sub_affiliate_code', $subAffiliateCode);								
					$bounceCollection = Mage::getModel('customertracking/record')->getCollection()
																			->addFieldToFilter('created_at', array( "lt" => $to,"gt"=>$from ))
																			->addFieldToFilter('affiliate_code', $affiliate->getAffiliateCode())
																			->addFieldToFilter('sub_affiliate_code', $subAffiliateCode)
																			->addFieldToFilter('status',array(3,4,5));
				}
				$totalRegistrations = $recordCollection->count();
				$totalBounces = $bounceCollection->count();
				$validateRegistrations = $totalRegistrations-$totalBounces;
				$revenue = 0;
				$z = 0;
				$tenth = 0;
				$twentyth = 0;
				$thirtyth = 0;
				$fortyth = 0;
				$fiftyth = 0;
				$valuedCustomerCount = 0;
				foreach ($recordCollection as $record) {
					$loginCount = $record->getLoginCount();
					if($loginCount<10){
						$z+=1;
					}elseif ($loginCount>=10 && $loginCount<20){
						$tenth+=1;
					}elseif ($loginCount>=20 && $loginCount<30){
						$twentyth+=1;
					}elseif ($loginCount>=30 && $loginCount<40){
						$thirtyth+=1;
					}elseif ($loginCount>=40 && $loginCount<50){
						$fortyth+=1;
					}elseif ($loginCount>=50){
						$fiftyth+=1;
					}
					
					$customerId = $record->getCustomerId();
					$orderCollection = Mage::getModel('sales/order')->getCollection()
																	->addFieldToFilter('customer_id',$customerId)
																	->addFieldToFilter('state','complete');
					if($orderCollection->count()){
						$valuedCustomerCount++;
					}
						foreach ($orderCollection as $order) {
								$revenue+=$order->getGrandTotal();
						}														
				}			
				$reportHtml.= '<table>
								<tr>
								<th>From</th>
								<th>To</th>
								<th>Affiliate</th>';
				if(!!$subAffiliateCode){
					$reportHtml.=	'<th>Sub Affiliate</th>';
				}else{
					$reportHtml.=	'<th>Including SubAffiliates</th>';
				}
				$reportHtml.=	'<th>Total Revenue</th>
								<th>Total Registrations</th>
								<th>Total Bounces</th>
								<th>Validated Registrations</th>
								</tr>
								<tr>
								<td>'.$resultFilter->getFrom().'</td>
								<td>'.$resultFilter->getTo().'</td>
								<td>'.$resultFilter->getAffiliate()->getAffiliateCode().'</td>';
				if(!!$subAffiliateCode){
					$reportHtml.=	'<td>'.$subAffiliateCode.'</td>';
				}elseif(!!$includeAllSubAffiliate){
					$reportHtml.=	'<th>Yes</th>';
				}else{
					$reportHtml.=	'<th>No</th>';
				}
				$reportHtml.=	'<td>'.$revenue.'</td>
								<td>'.$totalRegistrations.'</td>
								<td>'.$totalBounces.'</td>
								<td>'.$validateRegistrations.'</td>
								</tr>						
								</table> ';			
				$reportHtml.= '<table>
								<tr>
								<th>Effective Co-Reg</th>
								<th>Number Of Customer Who Have Made Purchase</th>
								<th>0x</th>	
								<th>1x</th>	
								<th>2x</th>	
								<th>3x</th>	
								<th>4x</th>	
								<th>5x And Above</th>							
								</tr>
								<tr>
								<td></td>
								<td>'.$valuedCustomerCount.'</td>
								<td>'.$z.'</td>
								<td>'.$tenth.'</td>
								<td>'.$twentyth.'</td>
								<td>'.$thirtyth.'</td>
								<td>'.$fortyth.'</td>
								<td>'.$fiftyth.'</td>							
								</tr>
								</table>';
			}
		}
		return  $reportHtml;
	}
}