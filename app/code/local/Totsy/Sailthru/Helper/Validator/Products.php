<?php

/**
 * @category    Totsy
 * @package     Totsy_Sailthru
 * @author      Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

class Totsy_Sailthru_Helper_Validator_Products extends Totsy_Sailthru_Helper_Validator
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
		$this->configAdd('categories', array(
			'type' => 'array',
			'NotEmpty' => array()
		));
		$this->configAdd('ages', array(
			'type' => 'array',
			'NotEmpty' => array()
		));
	}

}