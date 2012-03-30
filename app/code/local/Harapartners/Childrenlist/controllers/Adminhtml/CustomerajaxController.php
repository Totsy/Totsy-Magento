<?php

class Harapartners_Childrenlist_Adminhtml_CustomerajaxController extends Mage_Adminhtml_Controller_Action {

    protected function _initCustomer() {
        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = Mage::getModel('customer/customer');
        if ($customerId) {
            $customer->load($customerId);
        }
        Mage::register('current_customer', $customer);
        return $this;
    }

    public function indexobseleteAction(){
        $this->_initCustomer();
        /*$subscriber = Mage::getModel('newsletter/subscriber')
            ->loadByCustomer(Mage::registry('current_customer'));*/
		 $customer = Mage::registry('current_customer');
         if ($customer->getId()) {
            //delete operation
			/*if($itemId = (int) $this->getRequest()->getParam('delete')) {
                try {
                    Mage::getModel('wishlist/item')->load($itemId)
                        ->delete();
                }
                catch (Exception $e) {
                    Mage::logException($e);
                }
            }*/   
        }
        Mage::register('subscriber', $subscriber);
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/customer_edit_tab_newsletter_grid')->toHtml());
    }
    
    public function indexAction()
    {
        $this->_initCustomer();
        /*$this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/customer_edit_tab_reviews', 'admin.customer.reviews')
                ->setCustomerId(Mage::registry('current_customer')->getId())
                ->setUseAjax(true)
                ->toHtml()
        );*/
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('childrenlist/adminhtml_customer_edit_tab_childrenlist', 'admin.customer.childrenlist')
                ->setCustomerId(Mage::registry('current_customer')->getId())
                ->setUseAjax(true)
                ->toHtml()
        );
    }

}
