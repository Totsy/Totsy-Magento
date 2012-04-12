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

class Harapartners_Stockhistory_Adminhtml_TransactionController extends Mage_Adminhtml_Controller_Action {   
	
	protected $_mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv');
	
	protected function _getSession()
	{
		return Mage::getSingleton('adminhtml/session');
	}
	
	public function indexAction()
	{
		$this->loadLayout()
			->_setActiveMenu('stockhistory/transaction')
			->_addContent($this->getLayout()->createBlock('stockhistory/adminhtml_transaction_index'))
			->renderLayout();
	}

	public function printAction()
	{
		$this->loadLayout()
			->_setActiveMenu('stockhistory/transaction')
			->_addContent($this->getLayout()->createBlock('stockhistory/adminhtml_transaction_report_print'))
			->renderLayout();
	}
	
	public function newAction()
	{
		$this->_getSession()->setTransFormData(null);
		$this->getRequest()->setParam('action_type', Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_AMENDMENT);
		$this->_forward('edit');
	}
	
	public function editAction()
	{
		//Create new only!
		$data = $this->getRequest()->getParams();
		$data = $this->_getSession()->getTransFormData();
//		if(empty($data['vendor_id'])){
//			$data['vendor_id'] = $this->getRequest()->getParam('vendor_id');
//		}
//		if(empty($data['vendor_code'])){
//			$data['vendor_code'] = $this->getRequest()->getParam('vendor_code');
//		}
//		if(empty($data['po_id'])){
//			$data['po_id'] = $this->getRequest()->getParam('po_id');
//		}
//		if(empty($data['category_id'])){
//			$data['category_id'] = $this->getRequest()->getParam('category_id');
//		}
		
		if(!!$data){
        	Mage::unregister('stockhistory_transaction_data');
        	Mage::register('stockhistory_transaction_data', $data);
        }
		
		$this->loadLayout()
			->_setActiveMenu('stockhistory/transaction')
			->_addContent($this->getLayout()->createBlock('stockhistory/adminhtml_transaction_edit'))
			->renderLayout();
	}
	
	public function reportAction()
	{
		$data = $this->getRequest()->getParams();
		if(!!$data){
        	Mage::unregister('stockhistory_transaction_report_data');
        	Mage::register('stockhistory_transaction_report_data', $data);
        }
		$this->loadLayout()
			->_setActiveMenu('stockhistory/transaction')	
			->_addContent($this->getLayout()->createBlock('stockhistory/adminhtml_transaction_report'))
			->renderLayout();	
	}
	
	public function exportPoCsvAction()
	{
        $fileName   = 'stock_transaction_info_' . date('YmdHi'). '.csv';
        $content    = $this->getLayout()
            ->createBlock('stockhistory/adminhtml_transaction_report_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
        //$this->_redirect('*/*/index');	
	}
	
	public function exportCsvAction()
	{
        $fileName   = 'stock_transaction_info_' . date('YmdHi'). '.csv';
        $content    = $this->getLayout()
            ->createBlock('stockhistory/adminhtml_transaction_index_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
        //$this->_redirect('*/*/index');	
	}
	
	public function importCsvAction()
	{
		$this->loadLayout()
			->_setActiveMenu('stockhistory/transaction')	
			->_addContent($this->getLayout()->createBlock('stockhistory/adminhtml_transaction_import'))
			->renderLayout();
	}
	
	public function saveAction()
	{
		$data = $this->getRequest()->getPost();
		if(isset($data['form_key'])){
			unset($data['form_key']);
		}
		$this->_getSession()->setTransFormData($data);
		
		try{
			$model = Mage::getModel('stockhistory/transaction');
			if(!!$this->getRequest()->getParam('id')){
				$model->load($this->getRequest()->getParam('id'));
			}
			
			$model->importData($data)->save();
			$model->updateProductStock();
			
			$this->_getSession()->addSuccess(Mage::helper('stockhistory')->__('Transaction saved'));
			$this->_getSession()->setTransFormData(null);
		}catch(Exception $e){
			$this->_getSession()->addError($e->getMessage());
			$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			return;
		}
		$this->_redirect('*/*/index');
	}
	
	public function saveImportAction()
	{
		$data = $this->getRequest()->getParams();
		try{
			if(isset($_FILES) && !empty($_FILES)){
				foreach($_FILES as $key => $file){
					if(isset($file['name']) && file_exists($file['tmp_name'])){
						if(! in_array($file['type'], $this->_mimes)){
							throw new Exception('Please import a CSV file');
						}
						$uploader = new Varien_File_Uploader($key);
						$uploader->setAllowRenameFiles(false);
						$uploader->setFilesDispersion(false);
						$path = Mage::getBaseDir('var') . DS;
						$uploader->save($path, $file['name']);
						$statusOptions = Mage::helper('stockhistory')->getGridTransactionStatusArray();
						$row = 0;
						
						$fileName = $path . $file['name'];
						if(($fh = fopen($fileName, 'r')) !== FALSE){
							while(($fileData = fgetcsv($fh, 10000, ',', '"')) !== FALSE){
								if($row > 0){
									$vendorId 	= trim($fileData[0]);
									$poId 		= trim($fileData[1]);
									$categoryId	= trim($fileData[2]);
									$productId 	= trim($fileData[3]);
									$unitCost	= trim($fileData[4]);
									$qtyDelta 	= trim($fileData[5]);
									$comment	= trim($fileData[6]);
									
									$transaction = Mage::getModel('stockhistory/transaction');
									$dataObj = new Varien_Object();
									$dataObj->setData('vendor_id', $vendorId);
									$dataObj->setData('po_id', $poId);
									$dataObj->setData('category_id', $categoryId);
									$dataObj->setData('product_id', $productId);
									$dataObj->setData('unit_cost', $unitCost);
									$dataObj->setData('qty_delta', $qtyDelta);
									$dataObj->setData('comment', $comment);
									$dataObj->setData('status', Harapartners_Stockhistory_Model_Transaction::STATUS_PROCESSING);
									$dataObj->setData('action_type', Harapartners_Stockhistory_Model_Transaction::ACTION_DIRECT_IMPORT);
									
									$transaction->importData($dataObj)->save();
									$transaction->updateProductStock();

								}
								$row ++;
							}
						}
					}
				}
				$this->_getSession()->addSuccess($this->__('Stock Import succeeded'));
				$this->_redirect('*/*/index');	
			}else{
				Mage::throwException($this->__('Please choose a file'));
			}
		}catch(Exception $e){
			$this->_getSession()->addError($e->getMessage());
			$this->_redirect('*/*/importcsv');
		}
	}
	
}