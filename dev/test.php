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

/**************enable profiler in admin panel****************/

/*******your test code  *******/

/******************************/

Varien_Profiler::stop('mytimer');
/*create a block and output the
profiler*/

echo Mage::getSingleton('core/layout')->createBlock('core/profiler')->toHtml();         
echo'done';
