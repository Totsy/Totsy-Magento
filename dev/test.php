<?php
ini_set('memory_limit', '2G');	
$mageFilename = '../app/Mage.php';
require_once $mageFilename;
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);


$data['email'] = 'a';
$data['key'] = 'key';
$url = 'http://127.0.0.1/affiliate/remote/register';
$ch = curl_init();
//curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data);
curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt ( $ch, CURLOPT_URL, $url );
curl_setopt ( $ch, CURLOPT_POST, 0);
$result = curl_exec ( $ch );
curl_close ( $ch );
echo $result;


Varien_Profiler::enable();
Varien_Profiler::start('mytimer');
