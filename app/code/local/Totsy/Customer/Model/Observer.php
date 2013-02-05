<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Customer_Model_Observer
{
    const XML_PATH_REGISTER_EMAIL_TEMPLATE_AUTOGEN = 'customer/create_account/email_template_autogen';

    /**
     * Process any automatic login requests by inspecting the incoming
     * request for the 'auto_access_token' variable.
     *
     * @return Totsy_Customer_Model_Observer
     */
    public function autoAuthorization()
    {
        $request  = Mage::app()->getFrontController()->getRequest();
        $response = Mage::app()->getFrontController()->getResponse();

        if ($token = $request->getQuery('auto_access_token')) {
            $storeId = $request->has('auto_access_store')
                ? (int) $request->getQuery('auto_access_store')
                : 1;

            $email = Mage::getSingleton('core/encryption')->decrypt($token);
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(1)
                ->setStoreId($storeId)
                ->loadByEmail($email);

            $urlRedirect = trim(Mage::app()->getStore($storeId)->getBaseUrl(), '/')
                . $request->getOriginalPathInfo();

            if ($customer && $customer->getId()) {
                Mage::getSingleton('customer/session')
                    ->setCustomerAsLoggedIn($customer)
                    ->setCheckLastValidationFlag(false)
                    ->setData('CUSTOMER_LAST_VALIDATION_TIME', -1);

                $response->setRedirect($urlRedirect . '?auto=login');
                return $this;
            } else if (preg_match("/\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i", $email)) {
                // look for an affiliate code to register this customer under
                if ($affiliateCode = $request->getQuery('auto_access_affiliate')) {
                    $affiliate = Mage::getModel('affiliate/record')
                        ->loadByAffiliateCode($affiliateCode);

                    if ($affiliate && $affiliate->getId()) {
                        $session = Mage::getSingleton('customer/session');
                        $session->setAffiliate($affiliate);
                    }
                }

                $customer = Mage::getModel('customer/customer');
                $newPassword = $customer->generatePassword();
                $customer->setWebsiteId(1)
                    ->setStoreId($storeId)
                    ->setEmail($email)
                    ->setPassword($newPassword)
                    ->save();

                Mage::dispatchEvent(
                    'customer_register_success',
                    array('account_controller' => $this, 'customer' => $customer)
                );

                $templateParams = array(
                    'customer' => $customer,
                    'new_password' => $newPassword
                );

                $mailer = Mage::getModel('core/email_template_mailer');
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);

                // Set all required params and send emails
                $mailer->setSender(
                    Mage::getStoreConfig(
                        Mage_Customer_Model_Customer::XML_PATH_REGISTER_EMAIL_IDENTITY,
                        $storeId
                    )
                );
                $mailer->setStoreId($storeId);
                $mailer->setTemplateId(
                    Mage::getStoreConfig(
                        self::XML_PATH_REGISTER_EMAIL_TEMPLATE_AUTOGEN,
                        $storeId
                    )
                );
                $mailer->setTemplateParams($templateParams);
                $mailer->send();

                Mage::getSingleton('customer/session')
                    ->setCustomerAsLoggedIn($customer)
                    ->setCheckLastValidationFlag(false)
                    ->setData('CUSTOMER_LAST_VALIDATION_TIME', -1);

                $response->setRedirect($urlRedirect . '?auto=register');
                return $this;
            }

            $response->setRedirect('customer/account/login');
        }

        return $this;
    }
}
