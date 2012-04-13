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

//$importCsvFile 	= 'product_meta_'.$startId.'_to_'.$endId.'_store'.$storeId.'_attributeSetId'.$attributeSetId.'.csv';
$importCsvFile 	= 'affiliate.csv';
$delimiter		= ',';
$header			= array();
$row 			= 0;

if(($handle = fopen($importCsvFile,'r')) !== FALSE){
	while(($data = fgetcsv($handle))){
		$row++;
		if($row === 1){
			$header = $data;
			continue;
		}
		if($row!= 74||true){
			//Test if URL Rewrite exists already
			$affiliate = Mage::getModel('affiliate/record')->loadByAffiliateCode($data[4]);
			//$affiliate->setData('affiliate_id', $data[0]);
			$affiliate->setData('created_at',$data[1]);
			$affiliate->setData('status', $data[3]);
			$affiliate->setData('affiliate_name', ucwords($data[4]));
			$affiliate->setData('affiliate_code', strtolower($data[4]));	
			if($data[6]){
				$affiliate->setData('type', $data[6]);
			}else{
				$affiliate->setData('type', 1);
			}	
			
			$oldTrackingCode = json_decode($affiliate->getTrackingCode(),true);
		
			
			$newTrackingCode = json_decode($data[7],true);
			if(!!$oldTrackingCode){				
				if(isset($newTrackingCode['pixels']) && !!is_array($newTrackingCode['pixels'])){
					foreach ($oldTrackingCode as $index=>$record) {
						$trackingCode[$index] = $record;
					}					
					foreach ($newTrackingCode['pixels'] as $pixel) {
						if($pixel['code']!=$trackingCode[$pixel['page']]){
							$trackingCode[$pixel['page']].= $pixel['code'];
						}
					}				
				}
			}else{
				$trackingCode = array();
				if(isset($newTrackingCode['pixels']) && !!is_array($newTrackingCode['pixels'])){
					foreach ($newTrackingCode['pixels'] as $pixel) {
						if($trackingCode[$pixel['page']]!= $pixel['code']){
							$trackingCode[$pixel['page']].= $pixel['code'];
						}						
					}				
				}
			}
			if(!$affiliate->getSubAffiliateCode()){
				$affiliate->setSubAffiliateCode(strtolower($data[5]));
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
			//if($row > 5){
				//exit();
			//}
			//exit();
		}
	}
}

echo 'DONE!';
fclose($handle);

?>
