<?php
/**
 * @category    Totsy
 * @package     Totsy_WebsiteRestriction_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_WebsiteRestriction_Model_Observer
    extends Enterprise_WebsiteRestriction_Model_Observer
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

        if (!Mage::app()->getStore()->isAdmin()) {
            $dispatchResult = new Varien_Object(array('should_proceed' => true, 'customer_logged_in' => false));
            Mage::dispatchEvent('websiterestriction_frontend', array(
                'controller' => $controller, 'result' => $dispatchResult
            ));
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
                            $afterLoginUrl = Mage::getUrl();
                            if ('catalog' == $request->getModuleName() &&
                                $origPath = $request->getAlias('rewrite_request_path')
                            ) {
                                $afterLoginUrl = Mage::getBaseUrl() . $origPath;
                            }
                        }
                        // skip setting the post-login redirect URL when it points
                        // to the login action
                        if ('customer' != $request->getModuleName() &&
                            'account' != $request->getControllerName() &&
                            'login' != $request->getActionName()
                        ) {
                            Mage::getSingleton('core/session')->setWebsiteRestrictionAfterLoginUrl($afterLoginUrl);
                        }
                    }
                    elseif (Mage::getSingleton('core/session')->hasWebsiteRestrictionAfterLoginUrl()) {
                        $response->setRedirect(
                            Mage::getSingleton('core/session')->getWebsiteRestrictionAfterLoginUrl(true)
                        );
                        $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                    }
                    break;
            }
        }
    }
}
