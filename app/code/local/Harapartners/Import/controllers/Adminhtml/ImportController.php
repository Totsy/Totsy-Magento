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


class Harapartners_Import_Adminhtml_ImportController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('harapartners/import');
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('import/import')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getHpImportFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			$this->loadLayout();
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('import/adminhtml_import_edit'))
				->_addLeft($this->getLayout()->createBlock('import/adminhtml_import_edit_tabs'));
			$message = 'Please name your import, select a file and click on \'Import\'.' . '<br>' 
						.'Wait and leave the window open until everything is processed.';
			Mage::getSingleton('adminhtml/session')->addSuccess($message);
			$this->_initLayoutMessages('adminhtml/session');
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('import')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
	
	public function newAction() {
		$this->_forward('edit');
	}
	
	public function newByCategoryAction(){
		$categoryId = $this->getRequest()->getParam('category_id');
		Mage::getSingleton('adminhtml/session')->setHpImportFormData(array(
				'category_id' => $categoryId
		));
		$this->_forward('edit');
	}

	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			if(isset($_FILES['import_filename']['name']) && $_FILES['import_filename']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('import_filename');
					
					// Any extension would work
	           		$uploader->setAllowedExtensions(array('csv'));
					$uploader->setAllowRenameFiles(false);
				
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
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
		        $data['import_batch_id'] = $this->runDataflowProfile($_FILES['import_filename']['name']);
	  			$data['import_filename'] = $_FILES['import_filename']['name'];
	  			$data['import_status'] = Harapartners_Import_Model_Import::IMPORT_STATUS_UPLOADED;
			}
			$importModel = Mage::getModel('import/import');
			$importModel->setData($data)
						->setId($this->getRequest()->getParam('id'));
								
			try {
				$importModel->save();
				Mage::getSingleton('adminhtml/session')->setHpImportFormData(false);
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				//$this->updateProducts($importModel->getData('import_import_id'));
				
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setHpImportFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        //Mage::dispatchEvent('harapartners_import_after', $data);
        $this->_redirect('*/*/');
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
    
    public function runDataflowProfile($filename){	
    	
    	$profileId = 8; //hardcoded.  Need to put your profile id here
		$profile = Mage::getModel('dataflow/profile')->load($profileId);

		if (!!$profile && !!$profile->getId()) {
		    $gui_data = $profile->getData('gui_data');
		    $gui_data['file']['filename'] = $filename;
		    $profile->setData('gui_data', $gui_data);
		    $profile->save();
		  }else{
		  	Mage::getSingleton('adminhtml/session')->addError('The profile you are trying to save no longer exists');
		  }
		  Mage::register('current_convert_profile', $profile);
		  $profile->run();
		  $batchModel = Mage::getSingleton('dataflow/batch');
		  if ($batchModel->getId()) {
		  	if ($batchModel->getIoAdapter()) {
		  		$batchId = $batchModel->getId();
				return $batchId;
		  	}
		  }
    }
  

}
?>
