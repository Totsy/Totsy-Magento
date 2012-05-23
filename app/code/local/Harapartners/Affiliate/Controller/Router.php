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

class Harapartners_Affiliate_Controller_Router
    extends Mage_Core_Controller_Varien_Router_Abstract
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

        if (count($p) >= 2 && $p[0]=='a') {
            //Default Magento params in path: a/[affilate_code]?...
            //All other parameters need to be sent as GET params
            //Must at least specify affiliate code

        	/* $request->setModuleName('affiliate')
                ->setControllerName('register')
                ->setActionName('index')
                ->setParam('affiliate_code', $p[1]);*/
		
        //Andu modify to get a/affiliate_code&subid=xxx&aaa=bbb 	
        if(count($p) >= 2 && $p[0]=='a'){
        	$request->setModuleName('affiliate')
           			->setControllerName('register')
           			->setActionName('index');
        if(count($hp = explode('&',$p[1]))>=2){
          	$request->setParam('affiliate_code', $hp[0]);
          	foreach ($hp as $hhp) {
          		if(count($hhhp=explode('=',$hhp))==2){
            		$request->setParam($hhhp[0], $hhhp[1]);
           		}
         	 }          
         }else{
          $request->setParam('affiliate_code', $p[1]);
         }
        //	
        	
            if ($p[2] && is_numeric($p[2])) {
                $request->setParam('clickId', $p[2]);
            }

            return true;
        }elseif($request->getParam('a')){
            //Some legacy URLs: [keyword]?a=[affiliate_code]&...
            $matchResult = preg_split("/\?a=/", $identifier);
            if(!empty($matchResult[0])){
                $request->setModuleName('affiliate')
                    ->setControllerName('register')
                    ->setActionName('index')
                    ->setParam('affiliate_code', $request->getParam('a'))
                    ->setParam('keyword', $matchResult[0]);
                return true;
            } else {
                return false;
            }
        } elseif ($p[0] == 'affiliate') {
            //Remote register request go in here
            $request->setModuleName('affiliate')
                ->setControllerName('remote')
                ->setActionName('register');
            return true;           
        } elseif(count($p) >= 1 && $p[0]=='remote') {
            //Remote login logic
            //All other parameters need to be sent as GET params
            $request->setModuleName('affiliate')
                ->setControllerName('remote')
                ->setActionName('login');
            return true;
        } else {
            return false;
        }
    }
}
