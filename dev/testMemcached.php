<?php
ini_set('memory_limit', '2G');	
$mageFilename = '../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);

$memcache = new Memcache();
$memcache->connect(
		(string)Mage::getConfig()->getNode('global/cache/memcached/servers/server/host'), 
		(string)Mage::getConfig()->getNode('global/cache/memcached/servers/server/port')
);

$memcache->set('TEST_KEY', 'THIS IS A TEST!', true, 20);
echo $memcache->get('TEST_KEY');