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
    
    const DEFAULT_DATAFLOW_PROFILE_ID                     = 7;
    const DEFAULT_PRODUCT_STORE_CODE                     = 'admin';
    const DEFAULT_PRODUCT_WEBSITE_CODE                     = 'base';
    const DEFAULT_PRODUCT_ATTRIBUTE_SET                 = 'Totsy';
    const DEFAULT_PRODUCT_STATUS                         = 'Enabled';
    const DEFAULT_PRODUCT_TAX_CLASS                         = 'Taxable Goods';
    const DEFAULT_PRODUCT_SHORT_DESCRIPTION                = '';//'Welcome to Totsy!';
    const DEFAULT_PRODUCT_DESCRIPTION                    = '';//'Welcome to Totsy!';
    const DEFAULT_PRODUCT_WEIGHT                        = '1.0'; //Note all fields MUST be text
    const DEFAULT_PRODUCT_IS_IN_STOCK                    = '1'; //Note all fields MUST be text
    
    const PRODUCT_SKU_MAX_LENGTH                        = 17; //Restricted by DotCom
    const CONFIGURABLE_ATTRIBUTE_CODE                    = 'color,size';
    
    protected $_errorFilePath                 = null;
    protected $_errorFileWebPath             = null;
    protected $_errorMessages                 = array();
    protected $_requiredFields                 = array();
    protected $_confSimpleProducts             = array();
    protected $_confAttrCodes                = array();
    protected $_cleanImportDataArray		 = array();
    
    public function __construct(){
        $this->_errorFilePath = BP.DS.'var'.DS.'log'.DS.'import_error'.DS;
        $this->_errorFileWebPath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'var/log/import_error/';
    }
    
    
    // ================================================================== //
    // ===== Entry Points =============================================== //
    // ================================================================== //
    
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
//        Mage::register('current_convert_profile', $profile);
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
    
    public function runImport($importObjectId = null, $shouldRunIndex = false){
        $importObject = $this->_getUploadedImportModel($importObjectId);
        if(!$importObject || !$importObject->getId() || !$importObject->getData('import_batch_id')){
            //Nothing to run
            $this->_errorMessages[] = "Nothing to run!" . "\n";
        }else{
            
            // ===== disable indexing for better performance ===== //
            if(!$shouldRunIndex){
                Mage::unregister('batch_import_no_index');
                Mage::register('batch_import_no_index', true);
                //Note catalog URL rewrite is always refreshed after product save: afterCommitCallback()
            }
            // ===== dataflow, processing ===== //
            try{
                $batchModel = Mage::getModel('dataflow/batch')->load($importObject->getData('import_batch_id'));
                if (!!$batchModel && !!$batchModel->getId()){
                    $batchImportModel = $batchModel->getBatchImportModel(); //read line item
                    $adapter = Mage::getModel($batchModel->getAdapter()); //processor/writer
                    
                    //update status to 'lock' this import
                    $importObject->setStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_PROCESSING);
                    $importObject->save();
                    
                    //collection load is not possible due to the large amount of data per row
                    $batchId = $batchModel->getId();  
                    $importObjectIds = $batchImportModel->getIdCollection($batchId);
                    
                    //Get the required fields
                    $this->_prepareRequiredFields();
                    $row = 2; //Skip the header row
                    //Data cleaning, also scan through all imports for simple/config detection
                    $this->_cleanImportDataArray = array();
                    foreach ($importObjectIds as $importObjectId) {
                        $batchImportModel->load($importObjectId);
                        if (!$batchImportModel || !$batchImportModel->getId()) {
                            $this->_errorMessages[] = Mage::helper('dataflow')->__('Skip undefined row ' . $row . "\n");
                            continue;    
                        }
                        $importData = $batchImportModel->getBatchData();
                        $this->_cleanImportDataArray[$row] = $this->_importDataCleaning($importData, $importObject, $row);
                        $row++;
                    }
                    
                    //Core save logic
                    foreach($this->_cleanImportDataArray as $rowKey => $cleanImportData){
                    	try{
                            $adapter->saveRow($cleanImportData);
                            //Validation mode skips product save and the following re-index logic
        					if(!Mage::registry('import_validation_only')){
        						
        						//Harapartners, Jun, when import scritp updates an existing product, calculate the qty_delta for PO transactions
        						$qtyDelta = $cleanImportData['qty'];
        						//0 is a valid value
        						if(is_numeric(Mage::registry('temp_product_import_qty_delta_for_po_' . $cleanImportData['sku']))){
        							$qtyDelta = Mage::registry('temp_product_import_qty_delta_for_po_' . $cleanImportData['sku']);
        						}
        						$cleanImportData['qty_delta'] = $qtyDelta;
                            	$this->_savePurchaseOrderTransaction($cleanImportData, $importObject); //Save PO
        					}
                        } catch(Exception $ex) {
                            $this->_errorMessages[] = 'Error in row ' . $rowKey . ', ' . $ex->getMessage() . "\n";
                        }
                    }
                }
                $batchModel->delete();
            }catch(Exception $ex){
                $this->_errorMessages[] = $ex->getMessage() . "\n";
                $this->_errorMessages[] = "Execution terminated!" . "\n";
            }
        }
        
        //Clean up and error handling
        if(count($this->_errorMessages)){
            array_unshift($this->_errorMessages[], "Please make sure the header row has all required fields. All contents are case sensitive.");
            $filename = $this->_logErrorToFile();
            $importObject->setStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_ERROR);
            $importObject->setErrorMessage('<a href="' . $this->_errorFileWebPath . $filename . '">Error</a>');
            $importObject->save();
            Mage::throwException('There is an error processing the uploaded data. Please check the error log.');
        }
        
        if(!!Mage::registry('import_validation_only')){
        	$importObject->setStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_VALIDATED);
        }else{
        	$importObject->setStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_COMPLETE);
        }
        $importObject->save();
        return true;
    }
    
    
    // ================================================================== //
    // ===== Data Cleaning ============================================== //
    // ================================================================== //
    protected function _importDataCleaning($importData, $importObject, $row){
    
        // ----- Data from Import Form ----- //
        if(!$importObject->getData('vendor_id') || !$importObject->getData('vendor_code')){
            throw new Exception('Invalid vendor.');
        }
        $importData['vendor_code'] = $importObject->getData('vendor_code');
        if(!$importObject->getData('category_id')){
            throw new Exception('category_id is required');
        }
        $importData['category_ids'] = $importObject->getData('category_id');
        
        
        // ----- Data from Import Form ----- //
        foreach ($this->_requiredFields as $field) {
            if (empty($importData[$field])){
                switch ($field) {
                    case 'store':
                        $importData['store'] = self::DEFAULT_PRODUCT_STORE_CODE;
                        break;
                    case 'websites':
                        $importData['websites'] = self::DEFAULT_PRODUCT_WEBSITE_CODE;
                        break;
                    case 'attribute_set':
                        $importData['attribute_set'] = self::DEFAULT_PRODUCT_ATTRIBUTE_SET;
                        break;
                    case 'status':
                        $importData['status'] = self::DEFAULT_PRODUCT_STATUS;
                        break;
                    case 'sku':
                        $importData['sku'] = $this->_generateProductSku($importData, $importObject);
                        break;
                    case 'short_description':
                        $importData['short_description'] = self::DEFAULT_PRODUCT_SHORT_DESCRIPTION;
                        break;
                    case 'description':
                        $importData['description'] = self::DEFAULT_PRODUCT_DESCRIPTION;
                        break;
                    case 'weight':
                        $importData['weight'] = self::DEFAULT_PRODUCT_WEIGHT;
                        break;
                    case 'tax_class_id':
                        $importData['tax_class_id'] = self::DEFAULT_PRODUCT_TAX_CLASS;
                        break;
                    case 'is_in_stock':
                        $importData['is_in_stock'] = self::DEFAULT_PRODUCT_IS_IN_STOCK;
                        break;
                    default:
                    	if(!$importData['sku']){
                        	throw new Exception($field . ' is required in row #'.$row.'.');
                    	}
//                    	}else{
//                    		unset($importData[$field]);
//                    	}
                    	break;
                }
            }
        }
        
        // ----- Configurable/Simple products ----- //
        if($importData['type'] == 'configurable'){
            $importData['configurable_attribute_codes'] = implode(',', $this->_confAttrCodes);
            $importData['conf_simple_products'] = implode(',', array_values($this->_confSimpleProducts));
            $importData['visibility'] = 'Catalog, Search'; //All products are visible by default
            foreach($this->_confSimpleProducts as $rowKey => $rowData){
            	$this->_cleanImportDataArray[$rowKey]['visibility'] = 'Not Visible Individually';
            }
            $this->_confSimpleProducts = array();
            $this->_confAttrCodes = array();
        }else{
            $this->_confSimpleProducts[$row] = $importData['sku'];
            foreach(explode(',', self::CONFIGURABLE_ATTRIBUTE_CODE) as $confAttrCode){
                if(!empty($importData[$confAttrCode])
                        && !in_array($confAttrCode, $this->_confAttrCodes)
                ){
                    $this->_confAttrCodes[] = $confAttrCode;
                }
            }
            $importData['visibility'] = 'Catalog, Search'; //All products are visible by default
        }
        
        // ----- Default fields ----- //
        // HP Song remove empty column from array // 
        foreach($importData as $key => $value){
        	if($value==''){
        		unset($importData[$key]);
        	}
        }
        return $importData;
    }
    
    protected function _prepareRequiredFields(){
        $this->_requiredFields = array(
                //Magento core default
                'store', 'type', 'attribute_set', 'sku', 'websites', 'status', 'is_in_stock',
                //Totsy business logic
                'vendor_code', 'vendor_style', 'fulfillment_type', 
                'price', 'special_price', 'original_wholesale', 'sale_wholesale', 
                'shipping_method', 'weight', 
        );
        
        //Additional dataflow fields
        $fieldset = Mage::getConfig()->getFieldset('catalog_product_dataflow', 'admin');
        foreach ($fieldset as $code => $node) {
            if ($node->is('required')) {
                if(!in_array($code, $this->_requiredFields)){
                    $this->_requiredFields[] = $code;
                }
            }
        }
        
        //Totsy logic, description and short_description are no longer required
        foreach($this->_requiredFields as $key => $value){
        	if($value == 'description' || $value == 'short_description'){
        		unset($this->_requiredFields[$key]);
        	}
        }
        
    }
    
    protected function _generateProductSku($importData, $importObject){
        //$importObject must have 'vendor_id' here
        $sku = $importObject->getData('vendor_id') //The vendor_id is readable and can be 10^6 big
                . '-' . base_convert(time(), 10, 36) // 7 characters, including '-'
                . base_convert(rand(0, base_convert('zzz', 36, 10)), 10, 36); // 3 character
        $sku = substr($sku, 0, self::PRODUCT_SKU_MAX_LENGTH);
        return $sku;
    }
    
    
    // ================================================================== //
    // ===== Utilities ================================================== //
    // ================================================================== //
    protected function _getUploadedImportModel($importId = null){
        $import = Mage::getModel('import/import');
        if(!!$importId){
            $import->load($importId);
        }
        //If not specified, only runs the last 'uploaded' import
        if(!$import || !$import->getId()){
            $collection = Mage::getModel('import/import')->getCollection();
            $collection->addFieldToFilter('status', Harapartners_Import_Model_Import::IMPORT_STATUS_UPLOADED);
            $collection->getSelect()->limit(1);
            $import = $collection->getFirstItem();
        }
        if(!!$import 
                && !!$import->getId()
                && $import->getData('status') == Harapartners_Import_Model_Import::IMPORT_STATUS_UPLOADED
        ){
            return $import;
        }else{
            return null;
        }
    }
    
    protected function _savePurchaseOrderTransaction($importData, $importObject){
        $importDataObject = new Varien_Object($importData);
        $stockhistoryTransaction = Mage::getModel('stockhistory/transaction');
        
        //No update with 0 qty.
        if($importDataObject->getQtyDelta() == 0){
        	return;
        }
        
        //Note $importObject already passed validation here!
        //Transaction can only contain simple product!
        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $importData['sku']);
        if(!!$product && !!$product->getId() && $product->getTypeId() == 'simple'){
            $dataObj = new Varien_Object();
            $dataObj->setData('vendor_id', $importObject->getData('vendor_id'));
            $dataObj->setData('vendor_code', $importObject->getData('vendor_code'));
            $dataObj->setData('po_id', $importObject->getData('po_id'));
            $dataObj->setData('category_id', $importObject->getData('category_id'));
            $dataObj->setData('product_id', $product->getId());
            $dataObj->setData('product_sku', $product->getSku());
            $dataObj->setData('vendor_style', $product->getVendorStyle());
            $dataObj->setData('unit_cost', $product->getData('sale_wholesale'));
            $dataObj->setData('qty_delta', $importDataObject->getQtyDelta()? $importDataObject->getQtyDelta() : 0);
            $dataObj->setData('action_type', Harapartners_Stockhistory_Model_Transaction::ACTION_TYPE_EVENT_IMPORT);
            $dataObj->setData('comment', date('Y-n-j H:i:s'));
            $stockhistoryTransaction->importData($dataObj)->save(); //exceptions will be caught and added to $this->_errorMessage
        }
    }
    
    
    // ================================================================== //
    // ===== Error Logging ============================================== //
    // ================================================================== //
    protected function _logErrorToFile(){
        $filename = date('Y_m_d'). '_' . md5(time()) . '.txt';
        if(!is_dir($this->_errorFilePath)){
            mkdir($this->_errorFilePath, 0777);
        }        
        $errorFile = fopen($this->_errorFilePath . $filename, 'w');
        foreach($this->_errorMessages as $errorMessage){
            fwrite($errorFile, $errorMessage);
        }
        fclose($errorFile);
        return $filename;
    }
    
}