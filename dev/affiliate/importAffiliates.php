<?php
ini_set('memory_limit', '2G');	
$mageFilename = '../../app/Mage.php';
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
$importCsvFile 	= 'affiliate.csv';
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

		//Test if URL Rewrite exists already
		$affiliate = Mage::getModel('affiliate/record');
		//$affiliate->setData('affiliate_id', $data[0]);
		$affiliate->setData('created_at',$data[1]);
		$affiliate->setData('status', $data[3]);
		$affiliate->setData('affiliate_code', $data[4]);
		if($row != 240){
			$affiliate->setData('sub_affiliate_code', $data[5]);
		}
		$affiliate->setData('type', $data[6]);
		$affiliate->setData('tracking_code', $data[7]);
		//$affiliate->setData('referer_count', $data[8]);
		
		try {
			$affiliate->save();
		}catch (Exception $e){
			echo $e->getMessage();
			exit();
		}
		
		echo $row.' affiliate_code: '.$affiliate->getData('affiliate_code').' ID: '.$affiliate->getData('affiliate_id')."\n";
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
