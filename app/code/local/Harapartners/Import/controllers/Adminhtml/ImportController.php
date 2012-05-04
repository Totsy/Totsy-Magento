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

class Harapartners_Import_Adminhtml_ImportController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('harapartners/import');
        return $this;
    }   
 
    public function indexAction() {
        $this->_initAction()
            ->renderLayout();
    }
    
    public function newAction() {
        Mage::getSingleton('adminhtml/session')->setHpImportFormData(null);
        $this->_forward('edit');
    }
    
    public function newByCategoryAction(){
        $categoryId = $this->getRequest()->getParam('category_id');
        $category = Mage::getModel('catalog/category')->load($categoryId);
        if(!!$category && !!$category->getId()) {
            Mage::getSingleton('adminhtml/session')->setHpImportFormData(array(
                    'import_title' => $category->getName(), //Default title is the event name
                    'category_id' => $category->getId(),
                    'category_name' => $category->getName()
            ));
        }
        $this->_forward('edit');
    }

    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        //$data is used to pre-poluate form, by default load from session
        $data = Mage::getSingleton('adminhtml/session')->getHpImportFormData();

        //Do nothing for 'new'. With valid ID, load $data from DB
        if(!!$id){
            $model = Mage::getModel('import/import')->load($id);
            if(!!$model && !!$model->getId()){
                $data = $model->getData();
            }else{
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('import')->__('Invalid ID'));
                $this->_redirect('*/*/');
                return;
            }
        }
        
        if(!!$data){
            Mage::unregister('import_form_data');
            Mage::register('import_form_data', $data);
        }
        
        $this->loadLayout();
        //$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('import/adminhtml_import_edit'))
                ->_addLeft($this->getLayout()->createBlock('import/adminhtml_import_edit_tabs'));
        $message = 'For imports with 50+ proucts, please ONLY upload the file and run import offline.<br/>'
                    . 'For small imports, please wait and leave the window open until everything is processed.<br/>'
                    . 'If you want to see run big imports online. Please cut them in smaller pieces (<50).<br/>'
                    . 'Make sure associated products stays in the same file';
        Mage::getSingleton('adminhtml/session')->addNotice($message);
        Mage::getSingleton('adminhtml/session')->setHpImportFormData(null);
        $this->_initLayoutMessages('adminhtml/session');
        $this->renderLayout();
    }

    
    public function saveAction() {
        $data = $this->getRequest()->getPost();
        //save data in session in case of failure
        Mage::getSingleton('adminhtml/session')->setHpImportFormData($data);
        if(!$data){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('import')->__('Nothing to save.'));
            $this->_redirect('*/*/');
            return;
        }
        
        try {
            $id = $this->getRequest()->getParam('id');
            $model = Mage::getModel('import/import');
            if(!!$id){
                $model->load($id);
                if(!$model || !$model->getId()){
                    throw new Exception('Invalid ID');
                }
            }
            
            //Data preparation
            try {    
                /* Starting upload */    
                $uploader = new Varien_File_Uploader('import_filename');
                
                // Any extension would work
                   $uploader->setAllowedExtensions(array('csv'));
                $uploader->setAllowRenameFiles(false);
            
                // Set the file upload mode 
                // false -> get the file directly in the specified folder
                // true -> get the file in the product like folders 
                //    (file.jpg will go in something like /media/f/i/file.jpg)
                $uploader->setFilesDispersion(false);
                        
                // We set media as the upload dir
                $path = Mage::getBaseDir('var') . DS . 'import' . DS;
                $uploader->save($path, $_FILES['import_filename']['name'] );
                
            } catch (Exception $e) {
                  Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                  Mage::getSingleton('adminhtml/session')->setHpImportFormData($data);
                   $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                   return;
            }
        
            //this way the name is saved in DB
            $processorHelper = Mage::helper('import/processor');
            
            $data['import_batch_id'] = $processorHelper->runDataflowProfile($_FILES['import_filename']['name']);
              $data['import_filename'] = $_FILES['import_filename']['name'];
              $data['status'] = Harapartners_Import_Model_Import::IMPORT_STATUS_UPLOADED;
            
              $model->importData($data)->save();
            
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('import')->__('Save success.'));
            Mage::getSingleton('adminhtml/session')->setHpImportFormData(null); //clear form data from session
            
            //Processing and indexing
            $shouldRunImport = false;
            $shouldRunIndex = false;
            if(isset($data['action_type']) 
                    && $data['action_type'] == Harapartners_Import_Model_Import::ACTION_TYPE_PROCESS_IMMEDIATELY){
                $shouldRunImport = true;
            }elseif(isset($data['action_type']) 
                    && $data['action_type'] == Harapartners_Import_Model_Import::ACTION_TYPE_PROCESS_IMMEDIATELY_AND_INDEX
            ){
                $shouldRunImport = true;
                $shouldRunIndex = true;
            }
                
            if($shouldRunImport){
                try{
                    $processorHelper->runImport($model->getId(), $shouldRunIndex);
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('import')->__('The imported data has been processed.'));
                }catch(Mage_Core_Exception $mageE){
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('import')->__($mageE->getMessage()));
                }catch(Exception $e){
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('import')->__('There is an error processing the uploaded data.'));
                }
                
            }
            
            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('*/*/edit', array('id' => $model->getId()));
            }else{
                $this->_redirect('*/*/');
            }
            return;
        
        }catch(Mage_Core_Exception $mageE){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('import')->__($mageE->getMessage()));
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }catch(Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setHpImportFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }
    }
    
    public function massDeleteAction() {
        $importIds = $this->getRequest()->getParam('import');
        if(!is_array($importIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($importIds as $importId) {
                    $import = Mage::getModel('import/import')->load($importId);
                    $import->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($importIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

}