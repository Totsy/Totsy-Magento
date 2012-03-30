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

class Harapartners_Affiliate_RegisterController extends Mage_Core_Controller_Front_Action{

    public function indexAction(){       
    	$request = $this->getRequest();
        $affiliateCode = $request->getParam('affiliate');
        if(!!$request->getParam('siteid')){
        	$subAffiliateCode = $request->getParam('siteid');
        }elseif(!!$request->getParam('subid')){
        	$subAffiliateCode = $request->getParam('subid');
        }elseif(!!$request->getParam('subId')){
        	$subAffiliateCode = $request->getParam('subId');
        }elseif(!!$request->getParam('subID')){
        	$subAffiliateCode = $request->getParam('subID');
        }elseif(!!$request->getParam('siteId')){
        	$subAffiliateCode = $request->getParam('siteId');
        }elseif(!!$request->getParam('siteID')){
        	$subAffiliateCode = $request->getParam('siteID');
        }
        $otherParam = $request->getParam('other_param');  
        $affiliate = Mage::getModel('affiliate/record')->loadByAffiliateCode($affiliateCode);      
        $subAffiliateCodeArray = explode(',', $affiliate->getSubAffiliateCode());       
        if(!!$affiliate->getId()&& !!$affiliate->getStatus()){
            Mage::getSingleton('customer/session')->setData('affiliate_code', $affiliateCode);
            if(in_array($subAffiliateCode, $subAffiliateCodeArray)){
				Mage::getSingleton('customer/session')->setData('sub_affiliate_code', $subAffiliateCode)
													->setData('other_param', $otherParam);
        	}else{
        		Mage::getSingleton('customer/session')->setData('other_param', $otherParam);										
        	}
        }else{
        	//Mage::log('Invalid Affiliate'.$params['affiliateId'].$params['affiliateCode'].$params['registrationParam'].now());
        }
        $this->_redirect('customer/account/create');
        //Mage::getModel('haraparters/customerTracking')
    }   
}