<?php
ini_set('memory_limit', '2G');	
$mageFilename = '../../../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
$storeId			= '0';
Mage::app($mageRunCode, $mageRunType)->setCurrentStore($storeId);



/**
 * Declare variables
 * 
 * Path
 * importCsvFile
 * delmiter
 */
$productData	= array();
$stockData		= array();
$urlRewriteData	= array();

//$importCsvFile 	= 'product_meta_'.$startId.'_to_'.$endId.'_store'.$storeId.'_attributeSetId'.$attributeSetId.'.csv';
$importCsvFile 	= 'affiliate_tracking_cleaned.csv';
$delimiter		= ',';
$header			= array();
$row 			= 0;

if(($handle = fopen($importCsvFile,'r')) !== FALSE){
	while(($data = fgetcsv($handle, 4096, $delimiter, '"'))){
		$row++;
		if($row === 1){
			$header = $data;
			continue;
		}

		
		
//		customertracking_id 	int(10) 		UNSIGNED 	No 	None 	AUTO_INCREMENT 	Browse distinct values 	Change 	Drop 	Primary 	Unique 	Index 	Fulltext
//	
//		created_at 	datetime 			Yes 	NULL 		Browse distinct values 	Change 	Drop 	Primary 	Unique 	Index 	Fulltext
//	
//		updated_at 	datetime 			Yes 	NULL 		Browse distinct values 	Change 	Drop 	Primary 	Unique 	Index 	Fulltext
//	
//		affiliate_id 	int(10) 		UNSIGNED 	No 	0 		Browse distinct values 	Change 	Drop 	Primary 	Unique 	Index 	Fulltext
//	
//		customer_id 	int(10) 		UNSIGNED 	No 	0 		Browse distinct values 	Change 	Drop 	Primary 	Unique 	Index 	Fulltext
//	
//		registration_param 	text 	utf8_general_ci 		No 	None 		Browse distinct values 	Change 	Drop 	Primary 	Unique 	Index 	Fulltext
//	
//		login_count 	int(10) 		UNSIGNED 	No 	0 		Browse distinct values 	Change 	Drop 	Primary 	Unique 	Index 	Fulltext
//	
//		page_view_count
		
		
		
		
		
		
		
		
		
		//get customerId from customer model
		$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($data[5]);
		if(!$customer){
			$customerId = null;
		}else{
			$customerId = $customer->getId();
		}
		
		
		//customerTracking
		$customerTracking = Mage::getModel('customertracking/record');
		$customerTracking->setData('affiliate_id', $data[3]);
		$customerTracking->setData('customer_id', $customerId);
		$customerTracking->setData('customer_email', $data[5]);
		$customerTracking->setData('affiliate_code', $data[6]);
		$customerTracking->setData('sub_affiliate_code', $data[7]);
		$customerTracking->setData('registration_param', $data[8]);
		$customerTracking->setData('login_count', $data[9]);
		$customerTracking->setData('page_view_count', $data[10]);
		
		try {
			$customerTracking->save();
		}catch (Exception $e){
			echo $e->getMessage();
			exit();
		}
		
		echo $row.' affiliate_code: '.$customerTracking->getData('affiliate_code').' ID: '.$customerTracking->getDate('affiliate_id')."\n";
		//echo $row.' SKU: '.var_dump($data)."\n";
		//echo $row.' SKU: '.$product->get()."\n";
		if($row > 5){
			//exit();
		}
		//exit();
	
	}
}

echo 'DONE!';
fclose($handle);

?>
