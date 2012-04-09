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
		$affiliate = Mage::getModel('affiliate/record')->loadByAffiliateCode($data[4]);
		//$affiliate->setData('affiliate_id', $data[0]);
		$affiliate->setData('created_at',$data[1]);
		$affiliate->setData('status', $data[3]);
		$affiliate->setData('affiliate_name', $data[4]);
		$affiliate->setData('affiliate_code', $data[4]);		
		$affiliate->setData('type', $data[6]);
		$subAffiliateCode = $affiliate->getSubAffiliateCode();
		$trackingCode = json_decode($affiliate->getTrackingCode(),true);
		$newTrackingCode = json_decode($data[7],true);
		if(!!$subAffiliateCode){
			if(isset($newTrackingCode['code']) && !!$newTrackingCode['code']){
			$subAffiliateCode.=','.$newTrackingCode['code'];
			}
		}else{
			$subAffiliateCode = $newTrackingCode['code'];
		}
		if(!!$trackingCode){
			if(isset($newTrackingCode['pixel']) && !!is_array($newTrackingCode['pixel'])){
				foreach ($newTrackingCode['pixel'] as $pixel) {
					$trackingCode[$pixel['page']] = $pixel['code'];
				}				
			}
		}else{
			$trackingCode = array();
		if(isset($newTrackingCode['pixels']) && !!is_array($newTrackingCode['pixels'])){
				foreach ($newTrackingCode['pixels'] as $pixel) {
					$trackingCode[$pixel['page']] = $pixel['code'];
				}				
			}
		}
	if(!!$subAffiliateCode){
		$affiliate->setSubAffiliateCode($subAffiliateCode);
	}
	if($trackingCode){
		$affiliate->setTrackingCode(json_encode($trackingCode));
	}		
		//$affiliate->setData('tracking_code', $data[7]);
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
