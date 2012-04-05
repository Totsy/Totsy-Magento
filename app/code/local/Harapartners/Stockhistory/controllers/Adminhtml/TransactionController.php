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
		if(empty($data['vendor_id'])){
			$data['vendor_id'] = $this->getRequest()->getParam('vendor_id');
		}
		if(empty($data['vendor_code'])){
			$data['vendor_code'] = $this->getRequest()->getParam('vendor_code');
		}
		if(empty($data['po_id'])){
			$data['po_id'] = $this->getRequest()->getParam('po_id');
		}
		
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
		
		try{
			$model = Mage::getModel('stockhistory/transaction');
			if(!!$this->getRequest()->getParam('id')){
				$model->load($this->getRequest()->getParam('id'));
			}
			$model->validateAndSave($data);
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
						$statusOptions = Mage::helper('stockhistory')->getStatusOptions();
						$row = 0;
						
						$fileName = $path . $file['name'];
						if(($fh = fopen($fileName, 'r')) !== FALSE){
							while(($fileData = fgetcsv($fh, 10000, ',', '"')) !== FALSE){
								if($row > 0){
									$vendorId 	= trim($fileData[0]);
									$poId 		= trim($fileData[1]);
									$productId 	= trim($fileData[2]);
									$productSku = trim($fileData[3]);
									$vendorSku 	= trim($fileData[4]);
									$unitCost	= trim($fileData[5]);
									$qtyDelta 	= trim($fileData[6]);
									$comment	= trim($fileData[7]);
									
									$transaction = Mage::getModel('stockhistory/transaction');
									$transaction->setData('vendor_id', $vendorId);
									$transaction->setData('po_id', $productName);
									$transaction->setData('product_id', $productId);
									$transaction->setData('category_id', $category_id);
									$transaction->setData('product_sku', $productSku);
									$transaction->setData('vendor_sku', $vendorSku);
									$transaction->setData('unit_cost', $unitCost);
									$transaction->setData('qty_delta', $qtyDelta);
									$transaction->setData('comment', $comment);
									$transaction->setData('status', Harapartners_Stockhistory_Helper_Data::STATUS_PROCESSING);
									$transaction->setData('action_type', Harapartners_Stockhistory_Helper_Data::TRANSACTION_ACTION_DIRECT_IMPORT);
									$transaction->validateAndSave();
									
//									$transaction = Mage::getModel('stockhistory/transaction')->loadByEntityId($entityId);
//									if(! $transaction->getId()){
//										$transaction = Mage::getModel('stockhistory/transaction');
//										$transaction->setData('entity_id', $entityId);
//										$transaction->setData('product_name', $productName);
//										$transaction->setData('product_sku', $productSku);
//										$transaction->setData('vendor_sku', $vendorSku);
//										$transaction->setData('qty', $qtyDelta);
//										$transaction->setData('created_at', $createdAt);
//									}else{
//										$qty = $transaction->getData('qty') + $qtyDelta;
//										$transaction->setData('qty', $qty);
//										$transaction->setData('updated_at', date('Y-m-d H:i:s'));
//									}
//									$transaction->save();
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