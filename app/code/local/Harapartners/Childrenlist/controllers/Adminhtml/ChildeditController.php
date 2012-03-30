<?php

class Harapartners_Childrenlist_Adminhtml_ChildeditController extends Mage_Adminhtml_Controller_Action {
    
	public function getSession() {
		return Mage::getSingleton('adminhtml/session');
	}
    
    public function editAction() {	
        $this->_title($this->__('Customer'))
             ->_title($this->__('Customer Childlist'));
             
        if (!!$this->getRequest()->getParam('id')){
            $this->_title($this->__('Edit Child'));
        }else{
        	$this->_title($this->__('Add Child'));
        }
        $this->loadLayout();
        $this->_setActiveMenu('customer/edit');
        $this->_initLayoutMessages('adminhtml/session');
        $this->_addContent($this->getLayout()->createBlock('childrenlist/adminhtml_childrenlist_edit'));
        $this->renderLayout();
    }
    
    public function newAction() {
        $this->_forward('edit');
    }
    
    public function saveAction() {
    	$data = $this->getRequest()->getPost();
    	$this->getSession()->setChildEditFormData($data);
    	try{
    		$child = Mage::getModel('childrenlist/child')->validateAndLoadData($data);
    		$child->save();
    		$this->getSession()->addSuccess(Mage::helper('childrenlist')->__('The child information has been saved.'));
    		$this->getSession()->unsChildEditFormData();
    	}catch(Exception $e){
    		$this->getSession()->addError(Mage::helper('childrenlist')->__($e->getMessage()));
    		$editUrl = $this->getSession()->getEditPage();
    		$this->getResponse()->setRedirect($editUrl);
    		return;
    	}
    	if(!!$this->getRequest()->getPost('customer_id')){
    		$redirectParams['id'] = $this->getRequest()->getPost('customer_id');
    	}
        $this->_redirect('adminhtml/customer/edit', $redirectParams);
    }

    public function deleteAction() {
        $childId   = $this->getRequest()->getParam('id', false);
        $child = Mage::getModel('childrenlist/child')->load($childId);
        $customerId = $child->getCustomerId();
        $customerIdParam = (isset($customerId))?'/id/'.$customerId:'';
        try {
            Mage::getModel('childrenlist/child')->setId($childId)->delete();
            $this->getSession()->addSuccess(Mage::helper('childrenlist')->__('The child info has been deleted'));
            $this->getResponse()->setRedirect($this->getUrl('adminhtml/customer/edit'.$customerIdParam));
            return;
        } catch (Mage_Core_Exception $e) {
            $this->getSession()->addError($e->getMessage());
        } catch (Exception $e){
            $this->getSession()->addException($e, Mage::helper('childrenlist')->__('An error occurred while deleting this child info.'));
        }
        $this->_redirect('*/*/edit/',array('id'=>$childId));
    }
}

