<?php
ini_set('memory_limit', '2G');
ini_set('display_errors', 1);
$mageFilename = '../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);

$newFulfillmentType = 'dotcom';

$productsSku = array();

$filename = './products_sku.txt';

$fp = fopen($filename, 'r+');

if($fp) {
	while(!feof($fp))
	{
		$sku = fgets($fp, 4096);
		$productsSku[] = $filename;
	}
}

fclose($fp);

foreach($productsSku as $productSku) {
	$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSku);
	if(empty($product) || !$product->getId()) {
		continue;
	}
	$product->setData('fulfillment_type', $newFulfillmentType)
			->save();
			
	echo $productSku . ' DONE';
}

echo 'DONE';
