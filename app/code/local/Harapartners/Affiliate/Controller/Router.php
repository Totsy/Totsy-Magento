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

class Harapartners_Affiliate_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    /**
     * Modify request and set to no-route action
     * If store is admin and specified different admin front name,
     * change store to default (Possible when enabled Store Code in URL)
     *
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    public function match(Zend_Controller_Request_Http $request){
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
            		->setRedirect(Mage::getUrl('install'))
            		->sendResponse();
            exit;
        }		
        $identifier = trim($request->getPathInfo(), '/');
        $p = explode('/', $identifier);
        
        //must at least specify affiliate code
        if(count($p) >= 2 && $p[0]=='a'){
        	$request->setModuleName('affiliate')
        			->setControllerName('register')
        			->setActionName('index')
        			->setParam('affiliate_code', $p[1]);
        			//->setParam('clickid', $p[2]);
           //All other parameters need to be sent as GET params
        	return true;
        }elseif(count($p) >= 1 && $p[0]=='remote'){
        	$request->setModuleName('affiliate')
        			->setControllerName('remote')
        			->setActionName('login');
           //All other parameters need to be sent as GET params
        	return true;
        }else{
        	return false;
        }
    }
}
