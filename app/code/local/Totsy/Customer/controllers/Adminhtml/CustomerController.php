<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Adminhtml
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Customer_Adminhtml_CustomerController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     *
     */
    public function toggleDeactivatedAction()
    {
        $request = $this->getRequest();
        if ($customerId = $request->getParam('id')) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $customer->setDeactivated(!$customer->getDeactivated());
            $customer->save();

            $this->_redirect('adminhtml/customer/edit', array('id' => $customerId));
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                'Missing or invalid customer.'
            );

            $this->_redirect('adminhtml/customer/index');
        }
    }

    /**
     * Generate temporary password for customer action
     */
    public function generatePasswordAction()
    {
        $generatePassword = false;
        $request = $this->getRequest();
        if ($customerId = $request->getParam('id')) {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            if($customer->getId()) {
                try {
                    $randomPassword = $customer->generatePassword(8);
                    $customer->setPassword($randomPassword)->save();
                    //Sending email to customer with generated password
                    $templateId =  Mage::getModel('core/email_template')
                        ->loadByCode('_trans_Reset_Password_Admin')->getId();
                    $store = Mage::app()->getStore();
                    $email = $customer->getEmail();
                    Mage::getModel('core/email_template')->sendTransactional(
                        $templateId,
                        "sales",
                        $email,
                        NULL,
                        array(
                            "customer" => $customer,
                            "store" => $store
                        )
                    );
                    $generatePassword = true;
                } catch (Exception $exception) {
                    $generatePassword = false;
                }
            }
        }
        if(!$generatePassword){
            Mage::getSingleton('adminhtml/session')->addError(
                'Missing or invalid customer.'
            );
            $this->_redirect('adminhtml/customer/index');
        } else {
            Mage::getSingleton('adminhtml/session')->addSuccess('Password successfully generated.');
            $this->_redirect('adminhtml/customer/edit', array('id' => $customerId));
        }
    }
}
