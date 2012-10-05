<?php 
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

$full_path = dirname(dirname(__DIR__));

require_once( $full_path.'/app/Mage.php' );
umask(0);

$_SERVER['MAGE_IS_DEVELOPER_MODE'] = 1;
if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) {
    Mage::setIsDeveloperMode(true);
}


$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);	 

$feed = Mage::getModel('sailthru/feed');
$cache_object = $feed->getCacheHelper();
$cache_object->disableCache();

$cache = $cache_object->runner($full_path);
if ($cache !== false) {
	$feed->getFeedHelper()->sendHeaders();
	echo $cache;
	exit(0);
}

$storeId = Mage::app()->getStore($cache_object->getStoreCode())->getId(); //store id
//$storeId = Mage::app()->getStore()->getId(); //store id
Mage::app()->setCurrentStore($storeId);

$feed->runner();
$feed->getFeedHelper()->sendHeaders();
echo $feed->getOutPut();
exit(0);


?>