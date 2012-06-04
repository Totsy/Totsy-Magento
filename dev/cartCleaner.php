<?php	  
ini_set('memory_limit', '3G');
ini_set('max_input_time', 0);
require_once '../app/Mage.php';	  	  
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
//Mage::app();//->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
Mage::app($mageRunCode, $mageRunType);	  

echo 'Start:' . PHP_EOL;

$cleaner = Mage::getModel('rushcheckout/observer');
$cleaner->cleanExpiredQuotes();

echo 'End!' . PHP_EOL;