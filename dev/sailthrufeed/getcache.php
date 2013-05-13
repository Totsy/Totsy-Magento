<?php
$cache_file = dirname(dirname(__DIR__)).'/var/tmp/feed.json';
if (!file_exists($cache_file)){
	header("HTTP/1.0 404 File Not Found");
	die('ERROR 404. Cache file not found');
}
$cache = file_get_contents($cache_file);

header('Content-Type: application/json');
echo $cache;
exit(0);
?>