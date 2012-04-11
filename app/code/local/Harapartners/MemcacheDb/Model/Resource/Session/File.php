<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_MemcacheDb_Model_Resource_Session_File implements Zend_Session_SaveHandler_Interface {

	const SEESION_MAX_COOKIE_LIFETIME = 3155692600; // 100 years
	
    protected $_savePath;
	protected $_lifeTime;
	protected $_allowCompression = true;
	protected $_automaticCleaningFactor	= 50000; // reduce the frequency of session cleaning
	
	// ============================================================== //
	// ===== Magento Logic ========================================== //
	// ============================================================== //
	public function setSaveHandler() {
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
		session_save_path(Mage::getBaseDir('session'));
		return $this;
	}
	
	public function getLifeTime() {
		if (is_null($this->_lifeTime)) {
			$configNode = Mage::app()->getStore()->isAdmin() ?
					'admin/security/session_cookie_lifetime' : 'web/cookie/cookie_lifetime';
			$this->_lifeTime = (int) Mage::getStoreConfig($configNode);

			if ($this->_lifeTime < 60) {
				$this->_lifeTime = ini_get('session.gc_maxlifetime');
			}

			if ($this->_lifeTime < 60) {
				$this->_lifeTime = 3600; //one hour
			}

			if ($this->_lifeTime > self::SEESION_MAX_COOKIE_LIFETIME) {
				$this->_lifeTime = self::SEESION_MAX_COOKIE_LIFETIME; // 100 years
			}
		}
		return $this->_lifeTime;
	}

	// ============================================================== //
	// ===== Session Handling ======================================= //
	// ============================================================== //
	
	public function open($savePath, $sessionName) {
		$this->_savePath = $savePath;
		if (!is_dir($this->_savePath)) {
			mkdir($this->_savePath, 0777);
		}
		return true;
	}

	public function close() {
		return true;
	}

	public function read($id) {
		if($this->_allowCompression){
			return (string)@gzinflate(file_get_contents("$this->_savePath/sess_$id"));
		}else{
			return (string)@file_get_contents("$this->_savePath/sess_$id");
		}
	}

	public function write($id, $data) {
		if($this->_allowCompression){
			$data = gzdeflate($data, 9);
		}
		return file_put_contents("$this->_savePath/sess_$id", $data) === false ? false : true;
	}

	public function destroy($id) {
		$file = "$this->_savePath/sess_$id";
		if (file_exists($file)) {
			unlink($file);
		}
		return true;
	}

	public function gc($maxlifetime) {
		//$maxlifetime = min(array($maxlifetime, $this->getLifeTime()));
		$maxlifetime = $this->getLifeTime();
		if($this->_automaticCleaningFactor > 0){
            if ($this->_automaticCleaningFactor == 1 
            		|| rand(1, $this->_automaticCleaningFactor) == 1
            ){
                foreach (glob("$this->_savePath/sess_*") as $file) {
					if (file_exists($file) && filemtime($file) + $maxlifetime < time()) {
						unlink($file);
					}
				}
            }
        }
        return true;
	}
	
}