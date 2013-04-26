<?php

class Totsy_Sailthru_Adminhtml_FeedconfigController extends Mage_Adminhtml_Controller_Action{
    
    public function indexAction(){    
        $this->loadLayout()
            ->_setActiveMenu('sailthru/feedconfig')
            ->_addContent($this->getLayout()->createBlock('sailthru/adminhtml_feedconfig_index'))
            ->renderLayout();
    }  

    public function newAction(){
        Mage::getSingleton('adminhtml/session')->setSailthruFeedconfigFormData(null); //clear form data from session
        $this->_forward('edit');
    } 
    
    public function editAction(){
        $id = $this->getRequest()->getParam('id');
        //$data is used to pre-poluate form, by default load from session
        $data = Mage::getSingleton('adminhtml/session')->getSailthruFeedconfigFormData();

        //Do nothing for 'new'. With valid ID, load $data from DB
        if($id){
            $model = Mage::getModel('sailthru/feedconfig')->load($id);
            if(!!$model && !!$model->getId()){
                $data = $model->getData();
            }else{
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sailthru/feedconfig')->__('Invalid ID'));
                $this->_redirect('*/*/');
                return;
            }
        }
        
        if($data){
            Mage::unregister('sailthru_feedconfig_form_data');
            Mage::register('sailthru_feedconfig_form_data', $data);
        }
        
        $this->loadLayout()->_setActiveMenu('harapartners/feedconfig');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('sailthru/adminhtml_feedconfig_edit'));
        $this->renderLayout();
    }

    public function saveAction(){
        $data = $this->getRequest()->getPost();
        //save data in session in case of failure
        Mage::getSingleton('adminhtml/session')->setSailthruFeedconfigFormData($data);
        if(!$data){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sailthru')->__('Nothing to save.'));
            $this->_redirect('*/*/');
            return;
        }

        try {
            $id = $this->getRequest()->getParam('id');
            $feedConfigModel = Mage::getModel('sailthru/feedconfig');
            
            if($id){
                $feedConfigModel->load($id);
                if(!$feedConfigModel || !$feedConfigModel->getId()){
                    throw new Exception('Invalid ID');
                }
            }
            $feedConfigModel->importData($data)->save();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sailthru')->__('Save success.'));
            //clear form data from session
            Mage::getSingleton('adminhtml/session')->setSailthruFeedconfigFormData(null); 

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $feedConfigModel->getId()));
            }else{
                $this->_redirect('*/*/');
            }
            return;

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setSailthruFeedconfigFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }

    }
}

?>