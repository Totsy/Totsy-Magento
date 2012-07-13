<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Customer_Model_Observer
{
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

                $response->setRedirect($urlRedirect);
                return $this;
            } else if (preg_match("/\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i", $email)) {
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId(1)
                    ->setStoreId($storeId)
                    ->setEmail($email)
                    ->setPassword($customer->generatePassword())
                    ->save();

                Mage::getSingleton('customer/session')
                    ->setCustomerAsLoggedIn($customer)
                    ->setCheckLastValidationFlag(false)
                    ->setData('CUSTOMER_LAST_VALIDATION_TIME', -1);

                $response->setRedirect($urlRedirect);
                return $this;
            }

            $response->setRedirect('customer/account/login');
        }

        return $this;
    }
}
