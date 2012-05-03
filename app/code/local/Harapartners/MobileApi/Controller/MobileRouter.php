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

class Harapartners_MobileApi_Controller_MobileRouter extends Mage_Core_Controller_Varien_Router_Standard
{
    public function match(Zend_Controller_Request_Http $request){
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }        
        $identifier = trim($request->getPathInfo(), '/');
        $p = explode('/', $identifier);
        if(isset($p[0]) && $p[0] == 'mobileapi'){
            switch($p[1]){
                case 'event':
                    $request->setModuleName($p[0])
                        ->setControllerName($p[1])
                        ->setActionName(isset($p[3]) ? $p[3] : 'index')
                    ->setParam('id', $p[2]);
                    break;
                case 'user':
                    $request->setModuleName($p[0])
                        ->setControllerName($p[1])
                        ->setActionName(isset($p[3]) ? $p[3] : 'index')
                    ->setParam('id', $p[2]);
                    break;
                default:
                    $request->setModuleName($p[0])
                        ->setControllerName($p[1])
                        ->setActionName(isset($p[3]) ? $p[3] : 'index')
                    ->setParam('id', $p[2]);
            }
            
                    
            return true;
        }else{
            return false;
        }
    }
}