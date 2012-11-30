<?php

/**
 * @category    Totsy
 * @package     Totsy_Sailthru
 * @author      Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Sailthru_Helper_Validator {

	protected $config = array();
	protected $_vc = 'Zend_Validate_';
	protected $_errors = array();

	protected function configAdd($id,$array){
		$this->config[$id] = $array;
	}

	public function getErrors(){
		return $this->_errors;
	}

	public function process (&$data,$id=null){
		if (!is_array($this->config) 
			|| empty($this->config)) {
			return false;
		}
		if (!is_array($data) || empty($data)) {
			return false;
		}
		foreach ($this->config as $key => $value) {
			$this->by($data,$value,$key,$id);
		}

	}

	public function arrayKeyExists($key,&$array,&$error){
		if (array_key_exists($key, $array)){
			return true;
		}
		if (!is_array($error)){
			return true;
		}

		$error[] = 'Key "'.$key.'" does not exist in given array';
		$array[$key] = null;

		return false;
	}


	public function by(&$data,$conf,$element,$id=null){
		
		if (is_null($id)){
			$id = $data['id'];
		}

		foreach ($conf as $key => $value) {
			switch ($key){
				case 'type':
					if (substr($value,4)=='Zend'){
						$validator = new $value();
						if (!$validator->isValid($data[$element])){
							$this->_errors[$id][] = '"'.$element.'" is not valid type';
							continue;
						}
					}
					if ($value=='string'){
						if (!is_string($data[$element])){
							$this->_errors[$id][] = '"'.$element.'" is not valid string';	
							continue;
						}
					}
					if ($value=='array'){
						if (!is_array($data[$element])){
							$this->_errors[$id][] = '"'.$element.'" is not valid array';	
							continue;
						}
					}
				break;

				case 'gt':
					if (!is_array($data[$element])){
						$this->_errors[$id][] = '"'.$element.'" is not valid array';	
						continue;
					}
					if (count($data[$element])<=$value){
						$this->_errors[$id][] = '"'.$element.'" array length is equal to 0';	
						continue;
					}
				break;

				case 'Regex':
					foreach ($value as $reg){
						$class = $this->_vc.$key;
						$regex = new $class($reg);
						if (!$regex->isValid($data[$element])){
							$mes = '"'.$element.'" does not match required pattern';
							if ($conf['type']==$this->_vc.'Date'){
								$mes = '"'.$element.'" does not match date format "YYYY-MM-DD H:i:s"';
							}
							$this->_errors[$id][] = $mes;
							continue;
						}
					}
				break;

				default:
					$class = $this->_vc.$key;
					$validator = new $class($value);
					if (!$validator->isValid($data[$element])){
						$mes = '"'.$element.'" is not valid';
						if ($key == 'NotEmpty'){
							$mes =  '"'.$element.'" is empty';
						} else if ($key == 'Between') {
							$mes =  '"'.$element.'" is out of range';
						}
						$this->_errors[$id][] = $mes;
						continue;
					}
				break;
			}
		}

	}
}