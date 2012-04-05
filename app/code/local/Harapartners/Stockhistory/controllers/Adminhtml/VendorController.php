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

class Harapartners_Stockhistory_Adminhtml_VendorController extends Mage_Adminhtml_Controller_Action {   
	//protected $statusOptions = array('Pending' => 0, 'Processed' => 1, 'Failed' => 2);
	//protected $mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv');
	
	protected function _getSession() {
		return Mage::getSingleton('adminhtml/session');
	}
	public function indexAction() {
		$this->loadLayout()
			->_setActiveMenu('stockhistory/vendor')
			->_addContent($this->getLayout()->createBlock('stockhistory/adminhtml_vendor_index'))
			->renderLayout();
	}

	public function newAction() {
		$this->_getSession()->setVendorFormData(null);
		$this->_forward('edit');
	}
	
	public function editAction() {
		$id = $this->getRequest()->getParam('id', null);
		$data = $this->_getSession()->getVendorFromData();
		
		if(!!$id){
			$model  = Mage::getModel('stockhistory/vendor')->load($id);
			if(!!$model && !!$model->getId()){
				$data = $model->getData();
			}else{
				$this->_getSession()->addError(Mage::helper('stockhistory')->__('Invalid ID'));
				$this->_redirect('*/*/index');
				return;
			}
		}
		if(!!$data){
			Mage::unregister('stockhistory_vendor_data');
			Mage::register('stockhistory_vendor_data', $data);
		}
		$this->loadLayout()->_setActiveMenu('stockhistory/edit');
		$this->_addContent($this->getLayout()->createBlock('stockhistory/adminhtml_vendor_edit'));
		//$this->_addLeft($this->getLayout()->createBlock('stockhistory/adminhtml_vendor_edit_tabs'));
		$this->renderLayout();
		//$this->_redirect('*/*/index');
	}
	
	public function saveAction() {   
		$data = $this->getRequest()->getPost();
		if(isset($data['form_key'])){
			unset($data['form_key']);
		}
		$this->_getSession()->setVendorFormData($data);
		
		try{
			$model = Mage::getModel('stockhistory/vendor');
			if(!!$this->getRequest()->getParam('id')){
				$model->load($this->getRequest()->getParam('id'));
			}
			$model->validateAndSave($data);
			$this->_getSession()->addSuccess(Mage::helper('stockhistory')->__('Vendor saved successfully'));
			$this->_getSession()->setVendorFormData(null);
		}catch(Exception $e){
			$this->_getSession()->addError($e->getMessage());
       		$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
       		return;
		}
		$this->_redirect('*/*/index');
	}

	public function deleteAction()
	{
		$id = $this->getrequest()->getParam('id');
		$model = Mage::getModel('stockhistory/vendor')->load($id);
		if($model->getId()){
			try{
				$model->delete();
				$this->_getSession()->addSuccess(Mage::helper('stockhistory')->__('Delete the Record successfully'));
			}catch(Exception $e){
				$this->_getSession()->addError(Mage::helper('stockhistory')->__('Unable to Delete, please try again'));
			}
		}else{
			$this->_getSession()->addError(Mage::helper('stockhistory')->__('Unknown record, deletion failed'));
		}
		$this->_redirect('*/*/index');
	}
}