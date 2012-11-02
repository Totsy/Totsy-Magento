<?php

/**
 * @category    Totsy
 * @package     Totsy_Sailthru
 * @author      Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Sailthru_Helper_Validator_Events extends Totsy_Sailthru_Helper_Validator
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
		$this->configAdd('url', array(
			'type' => 'string',
			'NotEmpty' => array(),
			'Regex' => array(
				array('pattern' => '/totsy/'),
				array('pattern' => '/htm/')
			)
		));
		$this->configAdd('description', array(
			'type' => 'string',
			'NotEmpty' => array()
		));
		$this->configAdd('short', array(
			'type' => 'string',
			'NotEmpty' => array(),
			'StringLength' => array('max' => 90)
		));
		$this->configAdd('availableItems', array(
			'type' => $this->_vc.'Alpha',
			'NotEmpty' => array(),
			'StringLength' => array(
				'min' => 2,
				'max' => 3
			),
			'Regex' => array(
				array('pattern' => '/YES|NO/')
			)
		));
		$this->configAdd('image', array(
			'type' => 'string',
			'NotEmpty' => array(),
			'Regex' => array(
				array('pattern' => '/totsy/'),
				array('pattern' => '/jpg|png|jpeg/')
			)
		));
		$this->configAdd('image_small', array(
			'type' => 'string',
			'NotEmpty' => array(),
			'Regex' => array(
				array('pattern' => '/totsy/'),
				array('pattern' => '/jpg|png|jpeg/')
			)
		));
		$this->configAdd('discount', array(
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
		$this->configAdd('categories', array(
			'type' => 'array',
			'NotEmpty' => array()
		));
		$this->configAdd('ages', array(
			'type' => 'array',
			'NotEmpty' => array()
		));
		$this->configAdd('items', array(
			'type' => 'array',
			'NotEmpty' => array(),
			'gt' => 0
		));
		$this->configAdd('tags', array(
			'type' => 'string',
			'NotEmpty' => array(),
			'StringLength' => array('min' => 3)
		));
	}


}