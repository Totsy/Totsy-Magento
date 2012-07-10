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
     * Process any automatic registration requests by inspecting the incoming
     * request for the 'autoreg_token' variable.
     *
     * @param Varien_Event_Observer $obs
     *
     * @return Totsy_Customer_Model_Observer
     */
    public function processAutoregistration(Varien_Event_Observer $obs)
    {
        $request  = $obs->getEvent()->getControllerAction()->getRequest();
        $response = $obs->getEvent()->getControllerAction()->getResponse();

        if ($token = $request->get('autoreg_token')) {
            $autoreg = Mage::getModel('totsycustomer/autoregistration')
                ->loadByToken($token);
            if (!$autoreg->getId()) {
                $response->setRedirect('customer/account/login');
                return $this;
            }

            $customer = $autoreg->createCustomer();
            Mage::getSingleton('customer/session')
                ->setCustomerAsLoggedIn($customer);
        }

        return $this;
    }
}
