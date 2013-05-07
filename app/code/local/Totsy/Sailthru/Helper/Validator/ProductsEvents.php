<?php

/**
 * @category    Totsy
 * @package     Totsy_Sailthru
 * @author      Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

class Totsy_Sailthru_Helper_Validator_ProductsEvents extends Totsy_Sailthru_Helper_Validator
{

	public function __construct(){
		$this->configAdd('id', array(
			'type' => $this->_vc.'Int',
			'NotEmpty' => array(),
			'GreaterThan' => array('min' => 1)
		));
		$this->configAdd('name', array(
			'type' => $this->_vc.'Alnum',
			'NotEmpty' => array()
		));
		$this->configAdd('max_off', array(
			'type' => $this->_vc.'Int',
			'NotEmpty' => array(),
			'Between' => array(
				'min' => 1,
				'max' => 99
			)
		));
		$this->configAdd('start_date', array(
			'type' => $this->_vc.'Date',
			'NotEmpty' => array(),
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
		$this->configAdd('end_date', array(
			'type' => $this->_vc.'Date',
			'NotEmpty' => array(),
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
		$this->configAdd('products', array(
			'type' => 'array',
			'NotEmpty' => array(),
			'gt' => 0
		));
	}


}