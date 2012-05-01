<?php
ini_set('memory_limit', '2G');	
$mageFilename = '../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);

//$rushCron = Mage::getModel('rushcheckout/observer')->cleanExpiredQuotes();
$rushCron = Mage::getModel('service/service')->cleanCacheAfterSortRebuild();
//$rushCron = Mage::getModel('categoryevent/sortentry')->rebuildSortCorn();