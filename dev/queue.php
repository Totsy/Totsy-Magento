<?php

#define tmp and log path
$full = dirname(__DIR__);
$tmp = $full.'/var/tmp/';
$logs = $full.'/var/log/';

function shutdown(){
	global $logs;
	$a=error_get_last();
	if(!empty($a)){
		$fh = fopen($logs.'SailthruQueueError.log','a');
		fwrite($fh,"\n".date('[ Y-m-d H:i:s ] ')."\n");
		fwrite($fh,print_r($a,true));
		fclose($fh);
		exit(0);
	}
}
register_shutdown_function('shutdown');

require_once( $full.'/app/Mage.php' );
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
Mage::app($mageRunCode, $mageRunType);

$pid = getmypid();
$file = $tmp.basename(__FILE__,'.php').'Sailthru.pid';

isRunning();
try{
	$queue = Mage::getModel('emailfactory/sailthruqueue');	
	$ids = $queue->getQueueListIds();
	
	if (empty($ids['totalRecords'])){
		
		$message = 'Exit. Nothing to process, no pending calls in a queue'."\n";
		logMessage($message);
		execued();
		die($message);
	}
	foreach ($ids['items'] as $id){
		
		$message =  'processing queue #'.$id['id']."\n";
		logMessage($message);
		echo $message;
		 
		$qd = $queue->getQueueDetails($id['id']);
		$queue->processQueue($qd);
	}
} catch (Exception $e){
	$message = print_r($e,true);
	logMessage($message,'Error');
	execued();
	die($message);
}

execued();

function logMessage($message,$type = 'Logger'){
	global $logs;
	$fh = fopen($logs.'SailthruQueue'.$type.'.log','a');
	fwrite($fh,"\n".date('[ Y-m-d H:i:s ] ').$message."\n");
	fclose($fh);
}

function isRunning(){
	global $pid, $file;
	if (file_exists($file)){
		$message = "Cannot write to pid file '$file'. Program execution halted.\n";
		logMessage($message);
		die($message);
	}
	file_put_contents($file, $pid);
	logMessage('Starting Sailthru queue');
}

function execued(){
	global $file;
	if (file_exists($file)){
		unlink($file);
	}
	logMessage('Sailthru queue finished');
}  
?>