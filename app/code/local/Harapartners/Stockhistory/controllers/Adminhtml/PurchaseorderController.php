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

class Harapartners_Stockhistory_Adminhtml_PurchaseorderController extends Mage_Adminhtml_Controller_Action
{   
	//protected $statusOptions = array('Pending' => 0, 'Processed' => 1, 'Failed' => 2);
	protected $mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv');
	
	public function indexAction()
	{
		$this->loadLayout()
			->_setActiveMenu('stockhistory/purchaseorder')
			->_addContent($this->getLayout()->createBlock('stockhistory/adminhtml_purchaseorder_index'))
			->renderLayout();
	}

	public function exportCsvAction()
	{
		
        $fileName   = 'stock_history_info_' . date('YmdHi'). '.csv';
        $content    = $this->getLayout()
            ->createBlock('stockhistory/adminhtml_history_index_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
        //$this->_redirect('*/*/index');	
	}
	
	public function importCsvAction()
	{
		$this->loadLayout()
			->_setActiveMenu('stockhistory/history')	
			->_addContent($this->getLayout()->createBlock('stockhistory/adminhtml_history_import'))
			->renderLayout();
    
	}
	public function saveImportAction()
	{
		$data = $this->getRequest()->getParams();
		try{
			if(isset($_FILES) && !empty($_FILES)){
				foreach($_FILES as $key => $file){
					if(isset($file['name']) && file_exists($file['tmp_name'])){
						if(! in_array($file['type'], $this->mimes)){
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
									$entityId = trim($fileData[0]);
									$productName = trim($fileData[1]);
									$productSku = trim($fileData[2]);
									$size = trim($fileData[3]);
									$color = trim($fileData[4]);
									$vendorSku = trim($fileData[5]);
									$qtyDelta = trim($fileData[6]);
									$unitCost = trim($fileData[7]);
									$totalCost = trim($fileData[8]);
									$createdAt = trim($fileData[9]);
									$updatedAt = trim($fileData[10]);
									$status = trim($fileData[11]);
									$history = Mage::getModel('stockhistory/history');
									$history->setData('entity_id', $entityId);
									$history->setData('product_name', $productName);
									$history->setData('product_sku', $productSku);
									$history->setData('vendor_sku', $vendorSku);
									$history->setData('size', $size);
									$history->setData('color', $color);
									$history->setData('qty_delta', $qtyDelta);
									$history->setData('created_at', $createdAt);
									//$history->setData('updated_at', $updatedAt);
									$history->setData('unit_cost', $unitCost);
									$history->setData('total_cost', $totalCost);
									$history->setData('status', $statusOptions[$status]);
									$history->save();
									
									$report = Mage::getModel('stockhistory/report')->loadByEntityId($entityId);
									if(! $report->getId()){
										$report = Mage::getModel('stockhistory/report');
										$report->setData('entity_id', $entityId);
										$report->setData('product_name', $productName);
										$report->setData('product_sku', $productSku);
										$report->setData('vendor_sku', $vendorSku);
										$report->setData('qty', $qtyDelta);
										$report->setData('created_at', $createdAt);
									}else{
										$qty = $report->getData('qty') + $qtyDelta;
										$report->setData('qty', $qty);
										$report->setData('updated_at', date('Y-m-d H:i:s'));
									}
									$report->save();
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
	
	public function newAction()
	{
		$this->_forward('importCsv');
	}
	
	public function editAction()
	{
		$id = $this->getRequest()->getParam('id', null);
		$model  = Mage::getModel('stockhistory/history');
		if ($id) {
            $model->load((int) $id);
            if (!!$model->getId()) {
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if ($data) {
                    $model->setData($data)->setId($id);
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('awesome')->__('Example does not exist'));
                $this->_redirect('*/*/');
            }
        }
		
		
		Mage::register('po_data', $model);
		$this->loadLayout()->_setActiveMenu('stockhistory/edit');
		$this->_addContent($this->getLayout()->createBlock('stockhistory/adminhtml_history_edit'));
		$this->renderLayout();
		//$this->_redirect('*/*/index');
	}
	
//	public function massStatusAction()
//	{
//		$historyIds = $this->getRequest()->getParam('history');
//		if(!is_array($historyIds)){
//			Mage::GetSingleton('adminhtml/session')->addError($this->__("Please select IDs"));	
//		}else{
//			try{
//				foreach($historyIds as $historyId){
//					$history = Mage::getSingleton('stockhistory/report')
//							->load($historyId)
//							->setStatus($this->getRequest()->getParam('status'))
//							->setIsMassupdate(true)
//							->save();
//				}
//				$this->_getSession()->addSuccess($this->__('%d record(s) were successfully updated', count($historyIds)));
//			}catch(Exception $e){
//				$this->_getSession()->addError($e->getMessage());
//			}
//		}
//		$this->_redirect('*/*/index');
//	}
}