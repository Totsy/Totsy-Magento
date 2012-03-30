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
class Harapartners_Dropshipfactory_Adminhtml_DropshipController extends Mage_Adminhtml_Controller_Action {
	
	/**
     * index of fulfillment factory
     */
	public function indexAction() {
        $this->loadLayout()
        	->_setActiveMenu('dropshipfactory/index')
			->_addContent($this->getLayout()->createBlock('dropshipfactory/adminhtml_dropship_index'))
			->renderLayout();
    }
    
    /**
     * for display import csv page 
     */
	public function showImportCSVAction() {
    	$this->loadLayout()
    		->_setActiveMenu('dropshipfactory/edit')
			->_addContent($this->getLayout()->createBlock('dropshipfactory/adminhtml_dropship_edit'))
			->renderLayout();
    }
    
    /**
     * import csv file
     */
	public function importCSVAction() {
		try{
			$data = $this->getRequest()->getParams();
			if(isset($_FILES) && !empty($_FILES)){
				foreach($_FILES as $key => $file){
					if(isset($file['name']) && file_exists($file['tmp_name'])){
						$uploader = new Varien_File_Uploader($key);
						$uploader->setAllowRenameFiles(false);
						$uploader->setFilesDispersion(false);
						$path = Mage::getBaseDir('var') . DS;
						$uploader->save($path, $file['name']);
						
						$fileName = $path . $file['name'];
						
						Mage::getModel('dropshipfactory/service')->importTrackingFromCSV($fileName);
						
						unlink($fileName);
					}
				}
				$this->_getSession()->addSuccess($this->__('Tracking numbers have been imported successfully.'));
			}
		}
		catch(Exception $e){
			$this->_getSession()->addError($e->getMessage());
		}
		
		$this->_redirect('*/*/showImportcsv');
    }
    
    public function exportCSVAction() {    	
    	$content = $this->getLayout()
	    		->createBlock('dropshipfactory/adminhtml_dropship_index_grid')
	    		->getCsv();
	    		
	    $fileName   = Mage::getModel('dropshipfactory/service')->getExportCSVFilePath();
	    $this->_prepareDownloadResponse($fileName, $content);
    }
}