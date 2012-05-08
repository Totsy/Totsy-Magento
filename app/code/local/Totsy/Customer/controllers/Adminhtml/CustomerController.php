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
            Mage::getSingleton('admin/session')->addError(
                'Missing or invalid customer.'
            );

            $this->_redirect('adminhtml/customer/index');
        }
    }
}
