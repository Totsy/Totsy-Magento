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

class Harapartners_Affiliate_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract {
    /**
     * Modify request and set to no-route action
     * If store is admin and specified different admin front name,
     * change store to default (Possible when enabled Store Code in URL)
     *
     * @param Zend_Controller_Request_Http $request
     * @return boolean
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        if (! Mage::isInstalled ()) {
            Mage::app ()->getFrontController ()->getResponse ()->setRedirect ( Mage::getUrl ( 'install' ) )->sendResponse ();
            exit ();
        }

        $identifier = trim ( $request->getPathInfo (), '/' );
        $pathParams = explode ( '/', $identifier );

        if (count ( $pathParams ) >= 2 && $pathParams [0] == 'a') {
            //Default Magento params in path: a/[affilate_code]?...
            //All other parameters need to be sent as GET params
            //Must at least specify affiliate code

            //Andu modify to get a/affiliate_code&subid=xxx&aaa=bbb
            if (count ( $pathParams ) >= 2 && $pathParams [0] == 'a') {
                $request->setModuleName ( 'affiliate' )->setControllerName ( 'register' )->setActionName ( 'index' );
                if (count ( $rawAffiliateCode = explode ( '&', $pathParams [1] ) ) >= 2) {
                    $request->setParam ( 'affiliate_code', $rawAffiliateCode [0] );
                    foreach ( $rawAffiliateCode as $codeFragment ) {
                        if (count ( $codeParamPair = explode ( '=', $codeFragment ) ) == 2) {
                            $request->setParam ( $codeParamPair [0], $codeParamPair [1] );
                        }
                    }
                } else {
                    $request->setParam ( 'affiliate_code', $pathParams [1] );
                }
                if (isset ( $pathParams [2] ) && is_numeric ( $pathParams [2] )) {
                    $request->setParam ( 'clickId', $pathParams [2] );
                }

            }
            return true;
        } elseif ($request->getParam ( 'a' )) {
            //Some legacy URLs: [keyword]?a=[affiliate_code]&...
            $matchResult = preg_split ( "/\?a=/", $identifier );
            if (! empty ( $matchResult [0] )) {
                $request->setModuleName ( 'affiliate' )->setControllerName ( 'register' )->setActionName ( 'index' )->setParam ( 'affiliate_code', $request->getParam ( 'a' ) )->setParam ( 'keyword', $matchResult [0] );
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
