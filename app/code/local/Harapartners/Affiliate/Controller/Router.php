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
        if(substr($identifier,0,2)=='a/'){
        	$request->setModuleName('affiliate')
           			 ->setControllerName('register')
           			 ->setActionName('index')
            		->setParam('affiliate', $p[1])
            		->setParam('other_param', $p[2]);
        	return true;
        }else{
        	return false;
        }
    }
}
