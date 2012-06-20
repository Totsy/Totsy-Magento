<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_WebsiteRestriction
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Private sales and stubs observer
 *
 */
class Enterprise_WebsiteRestriction_Model_Observer
{
	/**
     * Implement website stub or private sales restriction
     *
     * @param Varien_Event_Observer $observer
     */
    public function restrictWebsite($observer)
    {
        /* @var $controller Mage_Core_Controller_Front_Action */
        $controller = $observer->getEvent()->getControllerAction();
        
        //var_dump(Mage::app()->getStore()->isAdmin());
        //exit();

        if (Mage::app()->getStore()->isAdmin()==false) {
            $dispatchResult = new Varien_Object(array('should_proceed' => true, 'customer_logged_in' => false));
            Mage::dispatchEvent('websiterestriction_frontend', array(
                'controller' => $controller, 'result' => $dispatchResult
            ));
            //Harapartners, Yang/Edward/Andu adding exempted modules
            if( preg_match( "/\/(totsyfbtab|totsy_signuptab|invitation|invite|faq|privacy|rss|affiliates|careers|aboutus|meet-the-moms|press|video-testimonials|being-green|totsy-blog|contact|inchoo_facebook|resetpassword|resetpasswordpost|facebook|inchoo|mobileapi|affiliate|terms|return-policy|privacy-policy|terms-of-use|brands|hsn-promo)\//i", Mage::app()->getRequest()->getRequestUri() ) ){
                return;
            }
            //Harapartners, Yang/Jun no restrictions for Affiliate Register Controller
            if(Mage::app()->getRequest()->getModuleName('affiliate') == 'affiliate'
                    && Mage::app()->getRequest()->getControllerName('register') == 'register'
            ){
                return;
            }
            
            if (!$dispatchResult->getShouldProceed()) {
                return;
            }
            if (!Mage::helper('enterprise_websiterestriction')->getIsRestrictionEnabled()) {
                return;
            }
            /* @var $request Mage_Core_Controller_Request_Http */
            $request    = $controller->getRequest();
            /* @var $response Mage_Core_Controller_Response_Http */
            $response   = $controller->getResponse();
            switch ((int)Mage::getStoreConfig(Enterprise_WebsiteRestriction_Helper_Data::XML_PATH_RESTRICTION_MODE)) {
                // show only landing page with 503 or 200 code
                case Enterprise_WebsiteRestriction_Model_Mode::ALLOW_NONE:
                    if ($controller->getFullActionName() !== 'restriction_index_stub') {
                        $request->setModuleName('restriction')
                            ->setControllerName('index')
                            ->setActionName('stub')
                            ->setDispatched(false);
                        return;
                    }
                    $httpStatus = (int)Mage::getStoreConfig(
                        Enterprise_WebsiteRestriction_Helper_Data::XML_PATH_RESTRICTION_HTTP_STATUS
                    );
                    if (Enterprise_WebsiteRestriction_Model_Mode::HTTP_503 === $httpStatus) {
                        $response->setHeader('HTTP/1.1','503 Service Unavailable');
                    }
                    break;

                case Enterprise_WebsiteRestriction_Model_Mode::ALLOW_REGISTER:
                    // break intentionally omitted

                // redirect to landing page/login
                case Enterprise_WebsiteRestriction_Model_Mode::ALLOW_LOGIN:
                    if (!$dispatchResult->getCustomerLoggedIn() && !Mage::helper('customer')->isLoggedIn()) {
                        // see whether redirect is required and where
                        $redirectUrl = false;
                        $allowedActionNames = array_keys(Mage::getConfig()
                            ->getNode(Enterprise_WebsiteRestriction_Helper_Data::XML_NODE_RESTRICTION_ALLOWED_GENERIC)
                            ->asArray()
                        );
                        if (Mage::helper('customer')->isRegistrationAllowed()) {
                            foreach(array_keys(Mage::getConfig()
                                ->getNode(
                                    Enterprise_WebsiteRestriction_Helper_Data::XML_NODE_RESTRICTION_ALLOWED_REGISTER
                                )
                                ->asArray()) as $fullActionName
                            ) {
                                $allowedActionNames[] = $fullActionName;
                            }
                        }
                        
                        // to specified landing page
                       if (Enterprise_WebsiteRestriction_Model_Mode::HTTP_302_LANDING === (int)Mage::getStoreConfig(
                           Enterprise_WebsiteRestriction_Helper_Data::XML_PATH_RESTRICTION_HTTP_REDIRECT
                       )) {
                            $allowedActionNames[] = 'cms_page_view';
                            $pageIdentifier = Mage::getStoreConfig(
                                Enterprise_WebsiteRestriction_Helper_Data::XML_PATH_RESTRICTION_LANDING_PAGE
                            );
                            if ((!in_array($controller->getFullActionName(), $allowedActionNames))
                                || $request->getParam('page_id') === $pageIdentifier) {
                                $redirectUrl = Mage::getUrl('', array('_direct' => $pageIdentifier));
                            }
                        }
                        // to login form
                        elseif (!in_array($controller->getFullActionName(), $allowedActionNames)) {
                            $redirectUrl = Mage::getUrl('customer/account/login');
                        }
                        
                        if ($redirectUrl) {
                            $response->setRedirect($redirectUrl);
                            $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        }
                        
                        if (Mage::getStoreConfigFlag(
                            Mage_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD
                        )) {
                            $afterLoginUrl = Mage::helper('customer')->getDashboardUrl();
                        } else {
                            $afterLoginUrl = Mage::getUrl('event');
                        }
                        
                        $origin_url = substr(Mage::getUrl(''),0,-1) .$controller->getRequest()->getOriginalPathInfo();
                        if ( preg_match('#sale|age|category#i',$origin_url) && $afterLoginUrl!=$origin_url ){
                        	$afterLoginUrl = $origin_url;
                        	unset($orgin_url);
                        }
                        $currentAfterLogin = Mage::getSingleton('core/session')->getWebsiteRestrictionAfterLoginUrl();
                        if (!preg_match('#sale|age|category#i',$currentAfterLogin)){
                        	Mage::getSingleton('core/session')->setWebsiteRestrictionAfterLoginUrl($afterLoginUrl);
                        }
                        unset($currentAfterLogin);
                    }
                    elseif (Mage::getSingleton('core/session')->hasWebsiteRestrictionAfterLoginUrl()) {
                        //Haraparnters, Jun, START: avoid unecessary redirect
                        $url = Mage::getSingleton('core/session')->getWebsiteRestrictionAfterLoginUrl(true);
                        if($url != Mage::helper('core/url')->getCurrentUrl()){
                            $response->setRedirect($url);
                            $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        }
                        //Haraparnters, Jun, END 
                    }
                    break;
            }
        }
    }

    /**
     * Attempt to disallow customers registration
     *
     * @param Varien_Event_Observer $observer
     */
    public function restrictCustomersRegistration($observer)
    {
        $result = $observer->getEvent()->getResult();
        if ((!Mage::app()->getStore()->isAdmin()) && $result->getIsAllowed()) {
            $restrictionMode = (int)Mage::getStoreConfig(
                Enterprise_WebsiteRestriction_Helper_Data::XML_PATH_RESTRICTION_MODE
            );
            $result->setIsAllowed((!Mage::helper('enterprise_websiterestriction')->getIsRestrictionEnabled())
                || (Enterprise_WebsiteRestriction_Model_Mode::ALLOW_REGISTER === $restrictionMode)
            );
        }
    }

    /**
     * Make layout load additional handler when in private sales mode
     *
     * @param Varien_Event_Observer $observer
     */
    public function addPrivateSalesLayoutUpdate($observer)
    {
        if (in_array((int)Mage::getStoreConfig(Enterprise_WebsiteRestriction_Helper_Data::XML_PATH_RESTRICTION_MODE),
            array(
                Enterprise_WebsiteRestriction_Model_Mode::ALLOW_REGISTER,
                Enterprise_WebsiteRestriction_Model_Mode::ALLOW_LOGIN
            ),
            true
        )) {
            $observer->getEvent()->getLayout()->getUpdate()->addHandle('restriction_privatesales_mode');
        }
    }
}
