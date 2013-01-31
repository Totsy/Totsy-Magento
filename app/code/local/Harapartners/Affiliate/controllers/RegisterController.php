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
        //Logic change, current affiliate overwrites previous ones (fixed after customer registration)
        //////Short-circuit logic, do NOT overwrite exiting affiliate
//        if(!!Mage::getSingleton('customer/session')->getAffiliateId()){
//            $this->_redirect('customer/account/create');
//            return;
//        }

        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $this->_redirect('/event/');
            return;
        }

        //Request data can be very dirty, clean up and validate
        $request = $this->getRequest();
        $affiliateCode = $this->formatCode($request->getParam('affiliate_code'));
        $affiliate = Mage::getModel('affiliate/record')->loadByAffiliateCode($affiliateCode);

        $affiliateInfo = $session->getData('affiliate_info');
        if (!$affiliateInfo) {
            $affiliateInfo = array();
        }

        if($affiliate && $affiliate->getId()) {
            if ($subAffiliateCode = $this->getRequestSubAffiliateCode()) {
                $affiliateInfo['sub_affiliate_code'] = $subAffiliateCode;
            }

            $regParams = isset($affiliateInfo['registration_param'])
                ? json_decode($affiliateInfo['registration_param'], true)
                : array();

            $regParams = array_merge($regParams, $request->getParams(), $request->getCookie());
            $affiliateInfo['registration_param'] = json_encode($regParams);

            //Additional logic: specific landing page after registration, background image can also be prepared here!
            //Harapartners, yang: plant cookie for landing page image
            $keyword = $request->getParam('keyword');
            $keywordCookieName = Mage::helper('affiliate')->getKeywordCookieName();
            Mage::getModel('core/cookie')->set($keywordCookieName, $keyword, 3600);
            //Harapartners, yang: end
            
            $session->setData('affiliate_id', $affiliate->getId());
            $session->setData('affiliate_info', $affiliateInfo);

            if ($request->getParam('r')){
                $redirect = preg_replace('/[^\w\d\-\_\/]/', '', $request->getParam('r'));
                if (!preg_match('/http|https/',$redirect)){
                    Mage::getSingleton('customer/session')->setAfterAuthUrl($redirect.'.html');
                }
             }
        }

        $this->_forward('create', 'account', 'customer');
    }

    //Alpha-numerical, lower case only, underscore allowed
    public function formatCode($code){
        return preg_replace("/[^a-z0-9_]/", "_", trim(strtolower((urldecode($code)))));
    }
    
    public function getRequestSubAffiliateCode(){
        $subAffiliateCode = '';
        $request = $this->getRequest();
        
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
        
        return $this->formatCode($subAffiliateCode);
    }
}