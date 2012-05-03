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

class Harapartners_Childrenlist_IndexController extends Mage_Core_Controller_Front_Action {
    
    /**
     * Get Children List
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Children List'));
        $this->renderLayout();
    }

    /**
     * Get Children List
     */
    public function editAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Children List'));
        $this->renderLayout();
    }
    
    public function isCustomerMatchChild($childId){
        $childId = Mage::app()->getRequest()->getPost('id', false);
        $child = Mage::getModel('childrenlist/child')->load($childId);
        return ($child->getCustomerId() == $this->_getSession()->getCustomer()->getId());
    }
    
    public function isEditMode(){
        $childInfo = $this->getRequest()->getPost();
        return !!$childInfo['id'];
    }
    
    /**
     * Change customer password action
     */
    public function editPostAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/index');
        }
        $customer = $this->_getSession()->getCustomer();
        
        if ($this->getRequest()->isPost()) {
            $childInfo = $this->getRequest()->getPost();

            if (($this->isEditMode())&&(!$this->isCustomerMatchChild($childInfo['id']))) {
                return $this->_redirect('*/*/index');
            }
             try {
                 $child = Mage::getModel('childrenlist/child');
                 if ($this->isEditMode()){
                    $child->load($childInfo['id']);
                 }else{
                     $childInfo['id'] = NULL;
                 }
                $childInfo['child_birthday'] = mktime(0,0,0,$childInfo['month'],$childInfo['day'],$childInfo['year']);
                foreach ($childInfo as $dataKey => $dataValue){
                    $child->setData($dataKey,$dataValue);
                }
                $child->save();
                $this->_getSession()->setCustomer($customer)
                    ->addSuccess($this->__('You child information has been saved.'));
                $this->_redirect('customer/account');
                return;
            } catch (Exception $e) {
                $this->_getSession()->setCustomer($customer)
                    ->addException($e, $this->__('Cannot save the child information.'));
                $this->_redirect('customer/account');
            }
        }
        $this->_redirect('*/*/index');
    }
    
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
}