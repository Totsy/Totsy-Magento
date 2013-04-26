<?php

class Totsy_Promotions_Adminhtml_BannerController extends Mage_Adminhtml_Controller_Action{
    
    public function indexAction(){    
        $this->loadLayout()
            ->_setActiveMenu('promotions/banner')
            ->_addContent($this->getLayout()->createBlock('promotions/adminhtml_banner_index'))
            ->renderLayout();
    }   

    public function newAction(){
        Mage::getSingleton('adminhtml/session')->setPromotionsBannerFormData(null); //clear form data from session
        $this->_forward('edit');
    } 
    
    public function editAction(){
        $id = $this->getRequest()->getParam('id');
        //$data is used to pre-poluate form, by default load from session
        $data = Mage::getSingleton('adminhtml/session')->getPromotionsBannerFormData();

        //Do nothing for 'new'. With valid ID, load $data from DB
        if($id){
            $model = Mage::getModel('promotions/banner')->load($id);
            if(!!$model && !!$model->getId()){
                $data = $model->getData();
            }else{
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('promotions/banner')->__('Invalid ID'));
                $this->_redirect('*/*/');
                return;
            }
        }
        
        if($data){
            Mage::unregister('promotions_banner_form_data');
            Mage::register('promotions_banner_form_data', $data);
        }
        
        $this->loadLayout()->_setActiveMenu('promotions/banner');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('promotions/adminhtml_banner_edit'));
        $this->renderLayout();
    }

    public function saveAction(){
        $data = $this->getRequest()->getPost();
        //save data in session in case of failure
        Mage::getSingleton('adminhtml/session')->setPromotionsBannerFormData($data);
        if(!$data){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('promotions')->__('Nothing to save.'));
            $this->_redirect('*/*/');
            return;
        }

        try {
            $id = $this->getRequest()->getParam('id');
            $bannerModel = Mage::getModel('promotions/banner');
            
            if($id){
                $bannerModel->load($id);
                if(!$bannerModel || !$bannerModel->getId()){
                    throw new Exception('Invalid ID');
                }
            }
            $bannerModel->importData($data)->save();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('promotions')->__('Save success.'));
            //clear form data from session
            Mage::getSingleton('adminhtml/session')->setPromotionsBannerFormData(null); 

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $bannerModel->getId()));
            }else{
                $this->_redirect('*/*/');
            }
            return;

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setPromotionsBannerFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }

    }

}   
