<?php
/**
 * Harapartners
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Harapartners License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.Harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Harapartners.com so we can send you a copy immediately.
 *
 */
class Mage_Customer_RevalidateController extends Mage_Core_Controller_Front_Action
{
    /**
    * Customer validation check
    */
    public function indexAction(){
    	if ( !!$this->_getSession()->setCheckLastValidationFlag() && $this->_getSession()->setCheckLastValidationFlag() ){
    		$this->loadLayout();
    		$this->renderLayout();
    	}else {
    		$this->_redirect('*/*/');
    	}
    }
    
    protected function _getSession(){
        return Mage::getSingleton('customer/session');
    }
    
    public function validationCheckPostAction(){
    	if ($this->getRequest()->isPost()) {
    		$login = $this->getRequest()->getPost('login');
    		$session = $this->_getSession();
    	    if (!empty($login['username']) && !empty($login['password'])) {
                try {
					$customer = Mage::getModel('customer/customer')
            			->setWebsiteId(Mage::app()->getStore()->getWebsiteId());
            		if ($customer->authenticate($login['username'], $login['password'], true)){
          		        if (!$session->getData('revalidate_before_auth_url')) {
			                $this->getResponse()->setRedirect($this->getUrl('customer/account'));
			        	}else {
			                $this->getResponse()->setRedirect($session->getData('revalidate_before_auth_url'));
			        	}
            		}
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        case Harapartners_Service_Model_Rewrite_Customer_Customer::EXCEPTION_INVALID_STORE_ACCOUNT:
                        	$message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    Mage::getSingleton('core/session')->addError($message);
                    $session->setUsername($login['username']);
                    $this->_redirect('*/*/');
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                Mage::getSingleton('core/session')->addError($this->__('Login and password are required.'));
                $this->_redirect('*/*/');
            }
    	}else {
    		$this->_redirect('*/*/');
    	}
    }
}