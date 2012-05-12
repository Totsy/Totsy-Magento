<?php
ini_set('memory_limit', '4G');	
ini_set("max_execution_time","1800000"); 
$magePath = '../';
$mageFilename = '../../../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
$storeId			= '0';
//Mage::app($mageRunCode, $mageRunType)->setCurrentStore($storeId);
//Varien_Profiler::enable();
Mage::app('admin');
echo "Start\n";

$ages = Mage::getModel('catalog/entity_attribute')->load(Mage::getResourceModel('eav/entity_attribute')
							            	->getIdByCode('catalog_product','ages')
								    );
								  
$ageOptions = array();

$valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
	->setAttributeFilter( $ages->getId() )
	->setStoreFilter( Mage_Core_Model_App::ADMIN_STORE_ID, false)
	->load();

foreach ($valuesCollection as $item) {
	$ageOptions[$item->getValue()] = $item->getId();
}

//echo print_r($ageOptions, 1);

$department = Mage::getModel('catalog/entity_attribute')->load(Mage::getResourceModel('eav/entity_attribute')
                                                                        ->getIdByCode('catalog_product','departments')
                                                                    );

$departmentOptions = array();

$valuesCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
        ->setAttributeFilter( $department->getId() )
        ->setStoreFilter( Mage_Core_Model_App::ADMIN_STORE_ID, false)
        ->load();

foreach ($valuesCollection as $item) {
        $departmentOptions[$item->getValue()] = $item->getId();
}

//echo print_r($departmentOptions, 1);

//$customer = Mage::getModel('customer/customer')->load(2);
//$rewardpoints = Mage::getModel('enterprise_reward/reward')->setCustomer($customer)->setWebsiteId(Mage::app()->getWebsite()->getId())->loadByCustomer();

                                          
//$importCsvFile 	= 'product_199994_to_end_cleaned.csv';
$importCsvFile 	= $argv[1];
$delimiter		= ',';
$header			= array();
$row 			= 0;
//Varien_Profiler::start('test');
if(($handle = fopen($importCsvFile,'r')) !== FALSE){
	while(($data = fgetcsv($handle, 4096, $delimiter, '"'))){
		//$productData = array();
		$row++;
		if($row === 1){
			$header = array_flip($data);
			$headerPosition = $data;
			//echo print_r($header, 1);
			continue;
		}
		/*
		$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $data[$header['sku']]);
		if(!$product){
			$product = Mage::getModel('catalog/product');
		}
		*/
		foreach ($data as $position=>$value){
				$productData[$headerPosition[$position]] = $data[$position];
				//$stockData[$header[$position]] = $data[$position];
		} 
		//$productData['attribute_set_id'] 	= '4';
		$productData['store'] 	= 'totsy';
		$productData['type_id']				= 'simple';
		$productData['attribute_set_id']	= '10';
		$productData['status']				= 'Enabled';
		$productData['visibility']			= Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH; //VISIBILITY_NOT_VISIBLE;
		$productData['tax_class_id']		= '2';
		$productData['shipping_method']		= '34';
		$productData['fulfillment_type']	= 'dotcom';
		//$productData['ages']	= $ageOptions[$data['ages']];;
		//$productData['departments']	= $departmentOptions[$data['departments']];
		/*	
		$product->addData( array(
			//'ages' => $ageOptions[$data['ages']]
			'ages' => "48,50,51"
		));
		echo $data[$header['departments']] . " " . $departmentOptions[$data[$header['departments']]] . "\n";
		$product->addData( array(
                        'departments' => array($departmentOptions[$data[$header['departments']]])
                ));

		//$product->setDepartments($departmentOptions[$data[$header['departments']]]);
		*/
		//$productData['in_stock_qty']	= $data[$header['qty']];
		
		//$product->setData($productData);
		//$product->setWebsiteIds(array('1'));
		try {
			//$product->save();
			
			//Mage::getModel('catalog/convert_adapter_product')->saveRow($productData);;
			Mage::getModel('import/productimport')->saveRow($productData);;
			
			//force save description
			$product =  Mage::getModel('catalog/product')->loadByAttribute('sku', $data[$header['sku']]);
	
			if(!!$product && !!$product->getId()) {
				$product->setDescription($data[$header['description']]);
        			//echo $product->getDescription();
				$product->save();
			}

		}catch (Exception $e){
			echo $e->getMessage() . "\n";
		}

		//if($row % 100 == 0) {
			echo $row . " SKU: " . $data[$header['sku']] ."\n";
		//}
	}
}
//Varien_Profiler::stop('test');
echo 'DONE!';
fclose($handle);
exit();
?>
