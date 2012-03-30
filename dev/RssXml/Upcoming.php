<?php 
// ========== Init setup ========== //
require_once( '../../app/Mage.php' );
Mage::app();

	header("Content-Type:text/xml");
	$url = Mage::getBaseUrl().'/rss/catalog/event/cid/8/store_id/1/upcoming/1';
	echo file_get_contents($url);