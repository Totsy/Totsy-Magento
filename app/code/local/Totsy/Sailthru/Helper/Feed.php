<?php

/**
 * @category    Totsy
 * @package     Totsy_Sailthru
 * @author      Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Sailthru_Helper_Feed {

	/**
	* send NO CACHE json headers 
	*/
	public function sendHeaders() {
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');
	}

}

?>