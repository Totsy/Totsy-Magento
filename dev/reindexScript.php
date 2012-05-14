<?php 
ini_set('memory_limit', '3G');
ini_set('max_input_time', 0);
require_once '../app/Mage.php';	  	  
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);	 

//By default, 3 indices are excluded from reindexing (See Harapartners_Service_Model_Rewrite_Index_Indexer for more details)
//Shop by Category/Age using a special search logic, so 'catalog_product_attribute', 'catalogsearch_fulltext' are no longer need
//Stock status is used for cart reservation logic, rebuilding 'cataloginventory_stock' will reset the reservation available QTY to be stock QTY (regardless of item counts in cart), thus this index is also skipped

foreach(Mage::getSingleton('index/indexer')->getProcessesCollection() as $process){
	echo $process->getData('indexer_code') . ' START: (' . date('Y-m-d H:i:s') . ' UTC)' . PHP_EOL;
	$process->reindexAll();
	echo $process->getData('indexer_code') . ' END: (' . date('Y-m-d H:i:s') . ' UTC)' . PHP_EOL;
}
echo 'Reindex done!' . PHP_EOL;