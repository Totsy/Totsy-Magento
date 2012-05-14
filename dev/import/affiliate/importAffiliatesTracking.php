<?php
ini_set('memory_limit', '2G');	
$mageFilename = __DIR__ . '/../../../app/Mage.php';
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
$importCsvFile 	        = $argv[1];
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
		
		//get customerId from customer model
		$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($data[5]);
		if(!$customer){
			$customerId = null;
		}else{
			$customerId = $customer->getId();
		}
		
		$affiliate = Mage::getModel('affiliate/record')->loadByAffiliateCode($data[6]);
		if (!$affiliate->getId()) {
			echo "Could not locate affiliate with code '$data[6]' on row $row.", PHP_EOL;
			continue;
		}

		//customerTracking
		$customerTracking = Mage::getModel('customertracking/record');
		$customerTracking->setData('affiliate_id', $data[3]);
		$customerTracking->setData('customer_id', $customerId);
		$customerTracking->setData('customer_email', $data[5]);
		$customerTracking->setData('affiliate_id', $affiliate->getId());
		$customerTracking->setData('affiliate_code', $data[6]);
		$customerTracking->setData('sub_affiliate_code', $data[7]);
		$customerTracking->setData('registration_param', $data[8]);
		$customerTracking->setData('login_count', $data[9]);
		$customerTracking->setData('page_view_count', $data[10]);
		
		try {
			$customerTracking->save();
		}catch (Exception $e){
			echo $e->getMessage(), PHP_EOL;
		}
	
	}
}

echo 'DONE!';
fclose($handle);

?>
