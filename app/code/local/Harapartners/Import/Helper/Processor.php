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

class Harapartners_Import_Helper_Processor extends Mage_Core_Helper_Abstract {
	
	const DEFAULT_DATAFLOW_PROFILE_ID = 7;
	
	protected $_errorFile 			= null;
	protected $_errorMessages 		= array();
	protected $_requiredFields 		= array();
	protected $_confSimpleProducts 	= array();
	protected $_purchaseOrderId		= null;
	
	
	public function runDataflowProfile($filename){	
		$profile = Mage::getModel('dataflow/profile')->load(self::DEFAULT_DATAFLOW_PROFILE_ID);

		if (!!$profile && !!$profile->getId()) {
		    $gui_data = $profile->getData('gui_data');
		    $gui_data['file']['filename'] = $filename;
		    $profile->setData('gui_data', $gui_data);
		    $profile->save();
		}else{
			throw new Exception('The profile you are trying to save no longer exists');
		  	Mage::getSingleton('adminhtml/session')->addError('The profile you are trying to save no longer exists');
		}
//		Mage::register('current_convert_profile', $profile);
		$profile->run();
		$batchModel = Mage::getSingleton('dataflow/batch');
		if ($batchModel->getId()) {
		  	if ($batchModel->getIoAdapter()) {
		  		$batchId = $batchModel->getId();
				return $batchId;
		  	}
		}
		
		return null;
    }
	
	protected function _logError($errorMessage){
		$errorMessage = 'Row '.$recordCount.': '.$ex->getMessage()."\n";
						$this->_errorMessages[] = $errorMessage;
						fwrite($this->getErrorFile(), $errorMessage);
	}
	
	protected function _getErrorFile($importModelId){
		if(!$this->_errorFile){
			$filename = $this->getErrorFilePath(). date('Y_m_d'). '_' . $importModelId . '.txt';
			$this->_errorFile = fopen($filename,'w');
			$this->_errorMessages = array(); 
		}
		return $this->_errorFile;
	}
	
	protected function _getErrorFilePath(){
		return Mage::getBaseDir('media').DS.'import'.DS.'errors'.DS;
	}
	
	protected function _getImportModel($importId = null){
		$import = Mage::getModel('import/import');
		if(!!$importId){
			$import->load($importId);
		}
		//If not specified, only runs the last 'uploaded' import
		if(!$import || !$import->getId()){
			$collection = Mage::getModel('import/import')->getCollection();
			$collection->addFieldToFilter('import_status', Harapartners_Import_Model_Import::IMPORT_STATUS_UPLOADED);
			$collection->getSelect()->limit(1);
			$import = $collection->getFirstItem();
		}
		return $import;
	}
	
	
	protected function _getRequiredFields(){
		$this->_requiredFields[] = 'store';
        $this->_requiredFields[] = 'type';  
        $this->_requiredFields[] = 'attribute_set';
        $this->_requiredFields[] = 'sku';
        $this->_requiredFields[] = 'websites';
        $this->_requiredFields[] = 'status';
        $this->_requiredFields[] = 'is_in_stock';
        
        
		$fieldset = Mage::getConfig()->getFieldset('catalog_product_dataflow', 'admin');
        foreach ($fieldset as $code => $node) {
        	if ($node->is('required')) {
                $this->_requiredFields[] = $code;
            }
        }
	}
	
	protected function _setSimpleProductVisibility(){
		foreach ($this->_confSimpleProducts as $sku) {
			$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
			if(!!$product && $product->getId()){
				$product->setData('visibility', '1');
				try {
					$product->save();
				}catch(Exception $e){
					$a=1;
				}
			}
		}
	}
	
	protected function _setProductSku($importData){
		$sku = '';
		if(isset($importData['vendor']) 
		&& isset($importData['vendor_style']) 
		&& isset($importData['color']) 
		&& isset($importData['size'])){
			$sku = $importData['vendor'].'-'.$importData['vendor_style'].'-'.$importData['color'].'-'.$importData['size'];
		}else{
			$string = 'totsy';
			$shuffled = str_shuffle($string);
			$sku = 'hp-'.date('H-i-s').$shuffled;
			$sku = str_replace(' ', '', $sku);
		}
		return $sku;
	}
	
	protected function _setRequiredAttributes($importData, $importObject){
		foreach ($this->_requiredFields as $field) {
			if (!isset($importData[$field])){
				switch ($field) {
					case 'store':
						$importData['store'] = 'admin';
						break;
					case 'websites':
						$importData['websites'] = 'base';
						break;
					case 'type':
						$importData['type'] = 'simple';
						break;
					case 'attribute_set':
						$importData['attribute_set'] = 'Totsy';
						break;
					case 'status':
						$importData['status'] = 'Enabled';
						break;
					case 'sku':
						$importData['sku'] = $this->_setProductSku($importData);
						break;
					case 'name':
						$importData['name'] = $importData['sku'];
						break;
					case 'description':
						$importData['description'] = 'description';
						break;
					case 'short_description':
						$importData['short_description'] = 'blurb';
						break;
					case 'weight':
						$importData['weight'] = '1';
						break;
					case 'price':
						$importData['price'] = '1';
						break;
					case 'tax_class_id':
						$importData['tax_class_id'] = 'Taxable Goods';
						break;
					case 'is_in_stock':
						$importData['is_in_stock'] = '1';
						break;
				}
			}
		}
		//Respect form data vendor_code
		if(!!$importObject && $importObject->getData('vendor_code')){
			$importData['vendor_code'] = $importObject->getData('vendor_code');
		}
		//Respect form data category ID
		if(!!$importObject && $importObject->getData('category_id')){
			$importData['category_ids'] = $importObject->getData('category_id');
		}
		
		if($importData['type'] == 'configurable'){
			$importData['configurable_attribute_codes'] = 'color,size';  //Hard Coded.  Need to enforce in template!
			$importData['conf_simple_products']			= implode(',',$this->_confSimpleProducts);
			$this->_setSimpleProductVisibility();
			unset($this->_confSimpleProducts);
			$this->_confSimpleProducts = array();
			$importData['visibility']					= 'Catalog, Search';
		}else{
			$importData['visibility']					= 'Catalog, Search'; //Need Logic for simple only.
			$this->_confSimpleProducts[] = $importData['sku'];
		}
		return $importData;
	}
	
	protected function _setPurchaseOrderInfo($importData, $importObject){
		$importDataObject = new Varien_Object($importData);
		
		$stockhistoryTransaction = Mage::getModel('stockhistory/transaction');
		$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $importData['sku']);
		if(!!$product && $product->getId()){
			$stockhistoryTransaction->setData('vendor_id', $importObject->getData('vendor_id'));
			$stockhistoryTransaction->setData('vendor_code', $importObject->getData('vendor_code'));
			$stockhistoryTransaction->setData('po_id', $importObject->getData('po_id'));
			$stockhistoryTransaction->setData('category_id', $importObject->getData('category_id'));
			$stockhistoryTransaction->setData('product_id', $product->getId());
			$stockhistoryTransaction->setData('vendor_sku', $product->getVendorStyle());
			$stockhistoryTransaction->setData('product_sku', $product->getSku());
			$stockhistoryTransaction->setData('unit_cost', $product->getData('sale_wholesale'));
			$stockhistoryTransaction->setData('qty_delta', $importDataObject->getQty());
			$stockhistoryTransaction->setData('action',2);//Harapartners_Stockhistory_Helper_Data::TRANSACTION_ACTION_EVENT_IMPORT);
			$stockhistoryTransaction->setData('comment', date('Y-n-j H:i:s'));
			try {
				$stockhistoryTransaction->save();
			}catch (Exception $e){
				//Error Log here
				/**
				 * Need Jun/Song for this message
				 * (string:285) SQLSTATE[HY000]: 
				 * General error: 1452 Cannot add or update a child row: 
				 * a foreign key constraint fails (`totsy_pdb1`.`stockhistory_report`, CONSTRAINT `FK_STOCKHISTORY_REPORT_VENDOR` FOREIGN KEY (`vendor_id`) REFERENCES `stockhistory_vendor` (`id`) ON DELETE SET NULL ON UPDATE CASCADE)
				 */
				$a = 1;
			}
		}
	}
	
	public function runImport($importObjectId = null){
		$importObject = $this->_getImportModel($importObjectId);
		if(!$importObject || !$importObject->getId() || !$importObject->getData('import_batch_id')){
			//Nothing to run
			return true;
		}

		// ===== dataflow, processing ===== //
		try{
			$batchModel = Mage::getModel('dataflow/batch')->load($importObject->getData('import_batch_id'));
			if (!!$batchModel && !!$batchModel->getId()){
				$batchImportModel = $batchModel->getBatchImportModel(); //read line item
				$adapter = Mage::getModel($batchModel->getAdapter()); //processor/writer
				
				//update status to 'lock' this import
				$importObject->setImportStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_PROCESSING);
				//$importObject->save();
				
				//collection load is not possible due to the large amount of data per row
				$batchId = $batchModel->getId();  
				$importObjectIds = $batchImportModel->getIdCollection($batchId);
				
				//Get the required fields
				$this->_getRequiredFields();
				
				foreach ($importObjectIds as $importObjectId) {	
					try{	
						$batchImportModel->load($importObjectId);
						if (!$batchImportModel || !$batchImportModel->getId()) {
							$this->_logError(Mage::helper('dataflow')->__('Skip undefined row'));
							continue;	
						}
						$importData = $batchImportModel->getBatchData();
						$importData = $this->_setRequiredAttributes($importData, $importObject);
						$adapter->saveRow($importData);

						/**
						 * PO Saves Here
						 */
						$this->_setPurchaseOrderInfo($importData, $importObject);
						
	
					} catch(Exception $ex) {
						//TODO
						$this->_logError(Mage::helper('dataflow')->__('Skip undefined row'));
					}  
				}
				if($hasErrors){
					$importObject->setImportStatus('import_import_error<a href="'.Mage::getBaseUrl().'media/import/errors/'.date('Y_m_d').'_'.$importObject->getId().'.txt">Error</a>');
					$importObject->save();   
				}else{
					$importObject->setImportStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_COMPLETE);
					$importObject->save();
				}
		  
			}
			$batchModel->delete();
			fclose(); //Handle
			return true;
		}catch(Exception $ex){
			return false;
		}
	}
	
}