<?php

/**
 * @category    Totsy
 * @package     Totsy_Sailthru
 * @author      Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Sailthru_Helper_Validator_Pending extends Totsy_Sailthru_Helper_Validator
{

	public function __construct(){
		$this->configAdd('name', array(
			'type' => $this->_vc.'Alnum',
			'StringLength' => array('min' => 1)
		));
		$this->configAdd('url', array(
			'type' => 'string',
			'StringLength' => array('min' => 1),
			'Regex' => array(
				array('pattern' => '/totsy/'),
				array('pattern' => '/htm/')
			)
		));
		$this->configAdd('start_date', array(
			'type' => $this->_vc.'Date',
			'Regex' => array( 'pattern' => '/'.
				'[\d]{4}'.  // YYYY
				'\-{1}'.    // -
				'[\d]{2}'.  // MM
				'\-{1}'.    // -
				'[\d]{2}'.  // DD
				'\s{1}'.    // [space]
				'[\d]{1,2}'.// H
				'\:{1}'.    // :
				'[\d]{2}'.  // i
				'\:{1}'.    // :
				'[\d]{2}'.  // s
				'/'
			)
		));
	}

	public function process(&$data){
		parent::process($data,'pending');
	}

}