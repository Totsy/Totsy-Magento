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
    
    protected function _getSession() {
        return Mage::getSingleton('adminhtml/session');
    }
    
    public function indexAction() {
        $this->loadLayout()
            ->_setActiveMenu('stockhistory/transaction')
            ->_addContent($this->getLayout()->createBlock('stockhistory/adminhtml_transaction_index'))
            ->renderLayout();
    }

    public function printAction() {
        $this->loadLayout()
            ->_setActiveMenu('stockhistory/transaction')
            ->_addContent($this->getLayout()->createBlock('stockhistory/adminhtml_transaction_report_print'))
            ->renderLayout();
    }
    
    public function newAmendmentByPoAction() {
        $poId = $this->getRequest()->getParam('po_id');
        $poOjbect = Mage::getModel('stockhistory/purchaseorder')->load($poId);
        
        if(!$poOjbect || !$poOjbect->getId()){
            $this->_getSession()->addError('Invalid Purchase Order.');
            $this->_redirect('*/adminhtml_purchaseorder/edit', array('id' => $poId));
            return;
        }
        
        $prepopulateData = array(
            'vendor_id'        => $poOjbect->getVendorId(),
            'vendor_code'    => $poOjbect->getVendorCode(),
            'po_id'            => $poOjbect->getId(),
            'category_id'    => $poOjbect->getCategoryId(),    
            'action_type'     => Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_AMENDMENT
        );
        
        $this->_getSession()->setTransFormData($prepopulateData);
        $this->_forward('edit');
    }
    
    public function editAction() {
        //Create new only! Not loading any existing ones
        $data = $this->getRequest()->getParams();
        $data = $this->_getSession()->getTransFormData();
        
        if(!!$data){
            Mage::unregister('stockhistory_transaction_data');
            Mage::register('stockhistory_transaction_data', $data);
        }
        
        $this->loadLayout()
            ->_setActiveMenu('stockhistory/transaction')
            ->_addContent($this->getLayout()->createBlock('stockhistory/adminhtml_transaction_edit'))
            ->renderLayout();
    }
    
    public function reportAction() {
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
    
    public function postBatchAmendmentAction() {
        $poId = $this->getRequest()->getParam('po_id');
        $poOjbect = Mage::getModel('stockhistory/purchaseorder')->load($poId);
        
        if(!$poOjbect || !$poOjbect->getId()){
            $this->_getSession()->addError('Invalid Purchase Order.');
            $this->_redirect('*/adminhtml_transaction/report', array('id' => $poId));
            return;
        }
        
        $prepopulateData = array(
            'vendor_id'        => $poOjbect->getVendorId(),
            'vendor_code'    => $poOjbect->getVendorCode(),
            'po_id'            => $poOjbect->getId(),
            'category_id'    => $poOjbect->getCategoryId(),    
            'action_type'     => Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_AMENDMENT
        );
        
        $productData = $this->getRequest()->getParam('qty_to_amend');
        $product = Mage::getModel('catalog/product');
        
        $isBatchSuccess = true;
        foreach($productData as $productSku => $amendmentData){
            $tempProduct = $product->loadByAttribute('sku', trim($productSku));
            if(!$tempProduct || !$tempProduct->getId()){
                $this->_getSession()->addError('Invalid Product SKU "' . trim($productSku) . '"');
                continue;
            }
            //Ignore empty rows
            if(empty($amendmentData['qty_to_amend'])){
                continue;
            }
            
            //Must validate non-empty rows
            if(!is_numeric($amendmentData['qty_to_amend'])
//                    || empty($amendmentData['qty_total']) 
//                    || !is_numeric($amendmentData['qty_total'])
//                    || empty($amendmentData['unit_cost']) 
//                    || !is_numeric($amendmentData['unit_cost'])
            ){
                $this->_getSession()->addError('Invalid Product Amendment Info for "' . trim($productSku) . '"');
                $isBatchSuccess = false;
                continue;
            }
            
            $qty_delta = 0;
            
            //if final qty is '00', remove item from PO
            if($amendmentData['qty_to_amend'] == '00') {
            	$qty_delta = '00';
            }
            else {
            	$qty_delta = $amendmentData['qty_to_amend'] - $amendmentData['qty_total']; //'qty_to_amend' is forced to be the new total
            }

            try{
                $tempdata = array_merge($prepopulateData, array(
                        'product_id'    => $tempProduct->getId(),
                        'qty_delta'        => $qty_delta,
                        'unit_cost'        => $amendmentData['unit_cost'],
                        'comment'        => 'Create by Batch Amendment'
                ));
                $transaction = Mage::getModel('stockhistory/transaction');
                $transaction->importData($tempdata);
                if($transaction->updateProductStock()){
                    $transaction->save();
                }else{
                    $this->_getSession()->addError('There is an error saving amendment Info for "' . trim($productSku));
                }
            }catch (Exception $e){
                $isBatchSuccess = false;
                $this->_getSession()->addError('Cannot create amendment Info for "' . trim($productSku) . '", ' . $e->getMessage());
            }
        }
        
        if($isBatchSuccess){
            $this->_getSession()->addSuccess('Batch processing completed.');
        }else{
            $this->_getSession()->addError('Some rows in the batch processing failed.');
        }
        
        $this->_redirect('*/adminhtml_transaction/report', array('po_id' => $poId));
        return;
    }
    
    public function exportPoCsvAction() {
        $fileName   = 'stock_transaction_info_' . date('YmdHi'). '.csv';
        $content    = $this->getLayout()
            ->createBlock('stockhistory/adminhtml_transaction_report_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
        //$this->_redirect('*/*/index');    
    }
    
    public function exportCsvAction() {
        $fileName   = 'stock_transaction_info_' . date('YmdHi'). '.csv';
        $content    = $this->getLayout()
            ->createBlock('stockhistory/adminhtml_transaction_index_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
        //$this->_redirect('*/*/index');    
    }
    
    public function importCsvAction() {
        $this->loadLayout()
            ->_setActiveMenu('stockhistory/transaction')    
            ->_addContent($this->getLayout()->createBlock('stockhistory/adminhtml_transaction_import'))
            ->renderLayout();
    }
    
    public function saveAction() {
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
    
    public function saveImportAction() {
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
                                    $vendorId     = trim($fileData[0]);
                                    $poId         = trim($fileData[1]);
                                    $categoryId    = trim($fileData[2]);
                                    $productId     = trim($fileData[3]);
                                    $unitCost    = trim($fileData[4]);
                                    $qtyDelta     = trim($fileData[5]);
                                    $comment    = trim($fileData[6]);
                                    
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
    
    /**
     * submit PO to DOTcom
     *
     */
    public function submitToDotcomAction() {
        $poObject = Mage::getModel('stockhistory/purchaseorder')->load($this->getRequest()->getParam('po_id'));
        if(!$poObject || !$poObject->getId()){
            $this->_getSession()->addError($this->__('Invalid PO.'));
        }
        
        //get report collection data from session
        $reportData = $this->_getSession()->getPOReportGridData();
        $itemsArray = array();
        foreach($reportData as $record) {
        	if($record['is_master_pack'] == 'Yes') {
        		$qty = $record['qty_total'];
        	}else{
        		$qty = $record['qty_sold'];
        	}
        	if($qty == 0) {
        		continue;
        	}
            //DotCom does NOT receive qty = 0 record
            if(!empty($record['sku']) && !empty($qty)){
                $itemsArray[$record['sku']] = $qty;
            }
        }
        
        $rsp = Mage::getModel('fulfillmentfactory/service_dotcom')->submitPurchaseOrdersToDotcom($poObject->generatePoNumber(), $itemsArray);

        $error = $rsp->purchase_order_error;
        if(!!$error) {
            $this->_getSession()->addError($this->__('Fail to submit PO to DOTcom. ' . $error->error_description));
        }else{
            $this->_getSession()->addSuccess($this->__('Sucessfully submit to DOTcom.'));
            //Update PO status
            try{
                $poObject->setStatus(Harapartners_Stockhistory_Model_Purchaseorder::STATUS_SUBMITTED);
                $poObject->save();
            }catch(Exception $e){
                $this->_getSession()->addError($e->getMessage());
            }
        }
        
        //clean collection in session
        Mage::getSingleton('adminhtml/session')->setPOReportGridData(null);
        
        $this->_redirect('*/*/report', array('po_id' => $this->getRequest()->getParam('po_id')));
    }
}