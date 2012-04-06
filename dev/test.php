<?php
ini_set('memory_limit', '2G');	
$mageFilename = '../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);
Varien_Profiler::enable();
Varien_Profiler::start('mytimer');

$product = Mage::getModel('catalog/product')->load(14);
$stock = $product->getStockItem()->getQty();
$a = 1;