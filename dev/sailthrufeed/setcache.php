<?php
$cache = dirname(dirname(__DIR__)).'/var/tmp/feed.json';
$url = 'http://www.totsy.com/dev/sailthrufeed/feed.php?';
$url.=http_build_query($_GET);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, ture);
//The number of seconds to wait while trying to connect
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 600); // time out 10 min. Use 0 to wait indefinitely
//The maximum number of seconds to allow cURL functions to execute.
curl_setopt($ch, CURLOPT_TIMEOUT, 600);  // time out 10 min

if( ! $result = curl_exec($ch)) { 
    echo 'ERROR: '.curl_error($ch); 
} 

if (file_exists($file)){
	$modified = 'Last modifification of the cache file on '.date(filectime($file)).
	'updated with change from '.date('Y-m-d H:i:s');
} else {
	$modified = 'It\'s new cache file. '.date('Y-m-d H:i:s');
}
$fh = fopen($cache,'w');
if (!$fh){
	die('ERROR: Cannot open cache file for reading. Check path and permitions');
}
fwrite($fh,$result);
fclose($fh);

$url = 'http://'.$_SERVER['HTTP_HOST'].'/dev/sailthrufeed/getcache.php';
echo $modified."<br>\n";
echo 'Please use this url to retreive cached data: '.
	'<a href="'.$url.'" target="_blank">'.$url.'</a>';
exit(0);

?>