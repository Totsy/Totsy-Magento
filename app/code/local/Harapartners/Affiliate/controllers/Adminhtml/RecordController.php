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

class Harapartners_Affiliate_Adminhtml_RecordController extends Mage_Adminhtml_Controller_Action{
	
	public function indexAction(){	
		$this->loadLayout()
			->_setActiveMenu('harapartners/affiliate/record')
			->_addContent($this->getLayout()->createBlock('affiliate/adminhtml_record_index'))
			->renderLayout();
    }   
    
	public function newAction(){
		Mage::getSingleton('adminhtml/session')->setAffiliateFormData(null); //clear form data from session
		$this->_forward('edit');
    } 
    
    public function editAction(){
		$id = $this->getRequest()->getParam('id');
		//$data is used to pre-poluate form, by default load from session
		$data = Mage::getSingleton('adminhtml/session')->getAffiliateFormData();

		//Do nothing for 'new'. With valid ID, load $data from DB
		if(!!$id){
			$model = Mage::getModel('affiliate/record')->load($id);
			if(!!$model && !!$model->getId()){
				$data = $model->getData();
			}else{
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliate')->__('Invalid ID'));
				$this->_redirect('*/*/');
				return;
			}
		}
		
		if(!!$data){
			Mage::unregister('affiliate_form_data');
			Mage::register('affiliate_form_data', $data);
		}
		
		$this->loadLayout()->_setActiveMenu('harapartners/affiliate');
		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		$this->_addContent($this->getLayout()->createBlock('affiliate/adminhtml_record_edit'));
		$this->renderLayout();
		
    }
    
	public function saveAction(){
		$data = $this->getRequest()->getPost();
		//save data in session in case of failure
		Mage::getSingleton('adminhtml/session')->setAffiliateFormData($data);
		if(!$data){
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliate')->__('Nothing to save.'));
        	$this->_redirect('*/*/');
        	return;
		}
		
		try {
			$id = $this->getRequest()->getParam('id');
			$model = Mage::getModel('affiliate/record');
			if(!!$id){
				$model->load($id);
				if(!$model || !$model->getId()){
					throw new Exception('Invalid ID');
				}
			}
			$model->importDataWithValidation($data)->save();
			
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('affiliate')->__('Save success.'));
			Mage::getSingleton('adminhtml/session')->setAffiliateFormData(null); //clear form data from session
			if ($this->getRequest()->getParam('back')) {
				$this->_redirect('*/*/edit', array('id' => $model->getId()));
			}else{
				$this->_redirect('*/*/');
			}
			return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setAffiliateFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
    }
    
    public function deleteAction(){
		$id = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('affiliate/record')->load($id);

		if ($model->getId()) {
			try{
				$model->delete();
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliate')->__('Unable to Delete, please try again'));
			}
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliate')->__('Unknown record, deletion failed'));
		}
		$this->_redirect('*/*/');
    }
    
    public function exportCsvAction(){
        $fileName   = 'affiliate record.csv';
        $content    = $this->getLayout()->createBlock('affiliate/adminhtml_record_index_grid')
            	->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    public function exportXmlAction(){
        $fileName   = 'affiliate record.xml';
        $content    = $this->getLayout()->createBlock('affiliate/adminhtml_record_index_grid')
            	->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }
    
}   
