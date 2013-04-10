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
            $model = Mage::getModel('sailthu/feedconfig')->load($id);
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
        
        $this->loadLayout()->_setActiveMenu('sailthru/feedconfig');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('sailthru/adminhtml_feedconfig_edit'));
        $this->renderLayout();
    }
}

?>