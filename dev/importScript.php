<?php	  
ini_set('memory_limit', '3G');
ini_set('max_input_time', 0);
//require_once '/home/stage/public_html/totsy/app/Mage.php';
require_once '../app/Mage.php';	  	  
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
//Mage::app();//->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
Mage::app($mageRunCode, $mageRunType);	  

$processing  = count(Mage::getModel('import/import')->getCollection()->addFieldToFilter('import_status','processing'));
if($processing > 0 ){	
	echo "no processing import status";
	exit;
}

$processing  = count(Mage::getModel('import/import')->getCollection()->addFieldToFilter('import_status','finalizing'));
if($processing > 0 ){	
	echo "no finalizing status";
	exit;
}

$importCollection = Mage::getModel('import/import')->getCollection()->addFieldToFilter('import_status', 'uploaded');
$importCollection->getSelect()->limit(1,0);
if(count($importCollection) < 1 ){
	echo "no uploaded status";
	exit;
}
$import = $importCollection->getFirstItem();
if(count(Mage::getModel('dataflow/batch')->getCollection()->addFieldToFilter('batch_id', $import->getData('import_batch_id'))) == 0){
	$import->delete();
	exit;
}
$batchModel = Mage::getModel('dataflow/batch')->load($import->getData('import_batch_id'));
$recordCount = 1;
/*
 * For Errors Start 
 */
$localPath = Mage::getBaseDir('media').DS.'import'.DS.'errors'.DS;
$filename = $localPath.date('Y_m_d').'_'.$import->getData('import_import_id').'.txt';
$errorFile = fopen($filename,'w');
$error = '';
$hasErrors = false;
/*
 * For Errors End
 */
$import->setImportStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_PROCESSING);
$import->save();
/*
 * Remove Cache Start
 */
//system('rm -rf ../var/cache/mage--*');
//system('rm -rf ../var/lightspeed/*');
/*
 * Remove Cache End
 */
/*
 * Reindex Start
 */
//$i = 1;
////Mage :: app( "default" );
//Mage::log("Started Rebuilding Search Index At: " . date("d/m/y h:i:s"));
//$sql = "truncate catalogsearch_fulltext;";
//$mysqli = Mage::getSingleton('core/resource')->getConnection('core_write');
//$mysqli->query($sql);
//while($i < 10){
//	echo $i;
//	$process = Mage::getModel('index/process')->load($i);
//	$process->reindexAll();
//	$i++;
//}
//Mage::log("Finished Rebuilding Search Index At: " . date("d/m/y h:i:s"));
/*
 * Reindex end
 */
if ($batchModel->getId()) {
	if ($batchModel->getIoAdapter()) {
		$batchId = $batchModel->getId();  
		$batchImportModel = $batchModel->getBatchImportModel();    
		$importIds = $batchImportModel->getIdCollection($batchId);    	
		$batchModel = Mage::getModel('dataflow/batch')->load($batchId);        
		$adapter = Mage::getModel($batchModel->getAdapter());  	
		foreach ($importIds as $importId) {	
			$recordCount++;	
			try{	
				$batchImportModel->load($importId);
				if (!$batchImportModel->getId()) {	
					$errors[] = Mage::helper('dataflow')->__('Skip undefined row');	
					continue;	
				}	
				
				$importData = $batchImportModel->getBatchData();	
				//$import->setImportStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_PROCESSING);
				//$import->save();
				//Jump in here to modify for configurable products
				$adapter->saveRow($importData);	
				if ($recordCount%20 == 0) {
            		echo 'Processed: '.$recordCount . ''.chr(13) . now();
				}
			} catch(Exception $ex) {
				$rowRemove = $recordCount + 1;
				$error = 'Row '.$recordCount.': '.$ex->getMessage()."\n";
				fwrite($errorFile, $error);
				$hasErrors = true;  	
			}  
		}
		if($hasErrors){
			//$import->setImportStatus('import_import_error<a href="'.Mage::getBaseUrl().'media/import/errors/'.date('Y_m_d').'_'.$import->getData('import_import_id').'.txt">Error</a>');
			$import->setImportStatus('import_import_error<a href="'.Mage::getBaseUrl().'media/import/errors/'.date('Y_m_d').'_'.$import->getData('import_import_id').'.txt">Error</a>');
			$import->save();   
		}else{
			$import->setImportStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_COMPLETE);
			$import->save();
		}
	    
	}
	  
}
$batchModel->delete();
fclose();
echo 'Batch Import Completed'; 
