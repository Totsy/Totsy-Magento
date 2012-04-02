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
	
	
	public function runImport($importId = null){
		// ===== get import object ===== //
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
		if(!$import || !$import->getId()){
			//Nothing to run
			return true;
		}

		// ===== dataflow, processing ===== //
			try{
			$batchModel = Mage::getModel('dataflow/batch')->load($import->getData('import_batch_id'));
	
			if (!!$batchModel 
					&& !!$batchModel->getId() 
					&& !!$batchModel->getIoAdapter()
					&& ($batchImportModel = $batchModel->getBatchImportModel()) //prepare import data reader
					&& ($adapter = Mage::getModel($batchModel->getAdapter())) //prepare data processor/writer
			) {
				
				//update status to 'lock' this import
				$import->setImportStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_PROCESSING);
				$import->save();
				
				//prepare error file
				//TODO: JUN, relative path should be saved in the DB, there should be a function getErrorFileBasePath()
				$localPath = Mage::getBaseDir('media').DS.'import'.DS.'errors'.DS;
				$filename = $localPath.date('Y_m_d').'_'.$import->getData('import_import_id').'.txt';
				$errorFile = fopen($filename,'w');
				$error = '';
				$hasErrors = false;
				
				$batchId = $batchModel->getId();  
				$importIds = $batchImportModel->getIdCollection($batchId);
				$recordCount = 0;
				foreach ($importIds as $importId) {	
					$recordCount++;	
					try{	
						$batchImportModel->load($importId);
						if (!$batchImportModel->getId()) {	
							$errors[] = Mage::helper('dataflow')->__('Skip undefined row');	
							continue;	
						}
						$importData = $batchImportModel->getBatchData();
						$adapter->saveRow($importData);	
	
					} catch(Exception $ex) {
						$error = 'Row '.$recordCount.': '.$ex->getMessage()."\n";
						fwrite($errorFile, $error);
						$hasErrors = true;  	
					}  
				}
				if($hasErrors){
					$import->setImportStatus('import_import_error<a href="'.Mage::getBaseUrl().'media/import/errors/'.date('Y_m_d').'_'.$import->getData('import_import_id').'.txt">Error</a>');
					$import->save();   
				}else{
					$import->setImportStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_COMPLETE);
					$import->save();
				}
		  
			}
			$batchModel->delete();
			fclose();
			return true;
		}catch(Exception $ex){
			return false;
		}
	}
	
}