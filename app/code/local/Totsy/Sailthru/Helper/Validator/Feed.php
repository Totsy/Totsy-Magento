<?php

/**
 * @category    Totsy
 * @package     Totsy_Sailthru
 * @author      Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Sailthru_Helper_Validator_Feed extends Totsy_Sailthru_Helper_Validator
{

	public function __construct(){
		$this->configAdd('events', array(
			'type' => 'array',
			'NotEmpty' => array()
		));
		$this->configAdd('pending', array(
			'type' => 'array',
			'NotEmpty' => array()
		));
		$this->configAdd('closing', array(
			'type' => 'array',
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
	}

	public function process(&$data){
		parent::process($data,'Feed');
	}
}