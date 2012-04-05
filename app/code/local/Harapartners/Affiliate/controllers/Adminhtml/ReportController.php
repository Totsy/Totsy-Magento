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

class Harapartners_Affiliate_Adminhtml_ReportController extends Mage_Adminhtml_Controller_Action{
	
	public function indexAction(){	
		$this->loadLayout();
		$tempBlock = $this->getLayout()->createBlock("affiliate/report")->setTemplate("affiliate/report.phtml");
		$this->getLayout()->getBlock('content')->append($tempBlock);
		$this->renderLayout();
    } 
    public function resultAction(){
    	$request = $this->getRequest();
    	$resultFilter = new Varien_Object;
    	$from = $request->getParam('from');
    	$to = $request->getparam('to');
    	if(!!$from && !!$to){
    		$resultFilter->setFrom($from);
    		$resultFilter->setTo($to);
    	}else{
    		$waringMessage = 'Please fill time period';
    		$resultFilter->setWarningMessage($waringMessage);
    	}
    	$affiliate = Mage::getModel('affiliate/record')->load($request->getParam('affiliate_code'));
    	if($affiliate->getStatus()!=1){
    		$waringMessage = 'Inactive Affiliate';
    		$resultFilter->setWarningMessage($waringMessage);
    	}else{
    		$resultFilter->setAffiliate($affiliate);
    	}
//    	if($subAffiliateCode = $request->getParam('sub_affiliate_code')){
//    		$resultFilter->setSubAffiliateCode($subAffiliateCode);
//    		$subAffiliateCodeArray = explode(',', $affiliate->getSubAffiliateCode()); 
//    		if(!in_array($subAffiliateCode, $subAffiliateCodeArray)){
//    			$waringMessage.= 'No such sub affiliate associate to the master affiliate.';
//    			$resultFilter->setWarningMessage($waringMessage);
//    		}
//    	}
    	if($request->getParam('all')){
    		$resultFilter->setIncludeAllSubAffiliate(true);
    	}
		Mage::unregister('resultFilter');
		Mage::register('resultFilter',$resultFilter); 
		if($request->getParam('report_type')=='totalregistrations'){
			$this->_forward('registration');
		}elseif($request->getParam('report_type')=='totalrevenue'){
			$this->_forward('revenue');
		}elseif($request->getParam('report_type')=='totalbounces'){
			$this->_forward('bounce');
		}elseif($request->getParam('report_type')=='effectivecoreg'){
			$this->_forward('effectivecoreg');
		}else{
			$this->_forward('index');
		}  	    	
    }
    
    public function registrationAction() {
    	$this->loadLayout();
		$reportBlock = $this->getLayout()->createBlock("affiliate/report")->setTemplate("affiliate/report.phtml");
		$resultBlock = $this->getLayout()->createBlock("affiliate/report")->setTemplate("affiliate/registration.phtml");
		$this->getLayout()->getBlock('content')->append($reportBlock)->append($resultBlock);
		$this->renderLayout();
    }
    public function revenueAction() {
    	$this->loadLayout();
		$reportBlock = $this->getLayout()->createBlock("affiliate/report")->setTemplate("affiliate/report.phtml");
		$resultBlock = $this->getLayout()->createBlock("affiliate/report")->setTemplate("affiliate/revenue.phtml");
		$this->getLayout()->getBlock('content')->append($reportBlock)->append($resultBlock);
		$this->renderLayout();
    }
    public function bounceAction() {
    	$this->loadLayout();
		$reportBlock = $this->getLayout()->createBlock("affiliate/report")->setTemplate("affiliate/report.phtml");
		$resultBlock = $this->getLayout()->createBlock("affiliate/report")->setTemplate("affiliate/bounce.phtml");
		$this->getLayout()->getBlock('content')->append($reportBlock)->append($resultBlock);
		$this->renderLayout();
    }
    public function effectivecoregAction() {
    	$this->loadLayout();
		$reportBlock = $this->getLayout()->createBlock("affiliate/report")->setTemplate("affiliate/report.phtml");
		$resultBlock = $this->getLayout()->createBlock("affiliate/report")->setTemplate("affiliate/effectivecoreg.phtml");
		$this->getLayout()->getBlock('content')->append($reportBlock)->append($resultBlock);
		$this->renderLayout();
    }
}