<?php
/* WSA Common
 *
 * @category   Webshopapps
 * @package    Webshopapps_Wsacommon
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */

class Webshopapps_Wsacommon_Helper_Data extends Mage_Core_Helper_Abstract
{

	/* 
	 * * Retrieves version of Magento and assigns a corresponding enterprise version
	 * */
	 
	public static function getVersion() {

		$version = Mage::getVersion();	
		$eeVersion = 0;
		if (version_compare($version, '1.6', '>=')) {
			if (version_compare($version, '1.11') >= 0) $eeVersion = 1.11;
			else if (version_compare($version, '1.10') >= 0) $eeVersion = 1.10;
			else if (version_compare($version, '1.6') >= 0 && !self::isEnterpriseEdition()) $eeVersion = 1.11;  //CE 1.6, EE unknown at present
			else if (version_compare($version, '1.9') >= 0) $eeVersion = 1.9;
			else if (version_compare($version, '1.8') >= 0) $eeVersion = 1.8;
			else if (version_compare($version, '1.7') >= 0) $eeVersion = 1.7;
			else if (version_compare($version, '1.6') >= 0) $eeVersion = 1.6; //EE 1.6
			else $eeVersion = 1.8;  // default to this if unsure, changed to assume is 1.4.1+
		}
		else {
			if (version_compare($version, '1.5') >= 0) $eeVersion = 1.10;
			else if (version_compare($version, '1.4.2') >= 0) $eeVersion = 1.9;
			else if (version_compare($version, '1.4.1') >= 0) $eeVersion = 1.8;
			else if (version_compare($version, '1.4.0') >= 0) $eeVersion = 1.7;
			else if (version_compare($version, '1.3.2.4') >= 0) $eeVersion = 1.6;
			else $eeVersion = 1.8;  // default to this if unsure, changed to assume is 1.4.1+
		}
		return $eeVersion;
	}
	
	/**
	 * This method will return just the 2nd part of the version number, thus allowing greater than/less than calculations to be performed
	 * Please convert to this call if possible
	 * Enter description here ...
	 */
	public static function getNewVersion() {
		$version = Mage::getVersion();	
		$eeVersion = 0;
		if (version_compare($version, '1.6', '>=')) {
			if (version_compare($version, '1.11') >= 0) $eeVersion = 11;
			else if (version_compare($version, '1.10') >= 0) $eeVersion = 10;
			else if (version_compare($version, '1.6') >= 0 && !self::isEnterpriseEdition()) $eeVersion = 11;  //CE 1.6, EE unknown at present
			else if (version_compare($version, '1.9') >= 0) $eeVersion = 9;
			else if (version_compare($version, '1.8') >= 0) $eeVersion = 8;
			else if (version_compare($version, '1.7') >= 0) $eeVersion = 7;
			else if (version_compare($version, '1.6') >= 0) $eeVersion = 6; //EE 1.6
			else $eeVersion = 8;  // default to this if unsure, changed to assume is 1.4.1+
		}
		else {
			if (version_compare($version, '1.5') >= 0) $eeVersion = 10;
			else if (version_compare($version, '1.4.2') >= 0) $eeVersion = 9;
			else if (version_compare($version, '1.4.1') >= 0) $eeVersion = 8;
			else if (version_compare($version, '1.4.0') >= 0) $eeVersion = 7;
			else if (version_compare($version, '1.3.2.4') >= 0) $eeVersion = 6;
			else $eeVersion = 8;  //default to this if unsure, changed to assume is 1.4.1+
		}
		return $eeVersion;
	}
	
	public static function checkItems($checkKey,$compare,$compare2) {
		if (Mage::getStoreConfig(base64_decode($checkKey))) {
			return true;
		}
		
		$temp2 = $_SERVER[base64_decode('U0VSVkVSX05BTUU=')]; 
		$found=false;
		if (self::checkItem($temp2,$compare,$compare2)) { $found= true; }
		else if (self::checkItem(substr($temp2,strpos($temp2,".")+1),$compare,$compare2)) { $found= true; }
		else if (self::checkItem('www.'.$temp2,$compare,$compare2)) { return true; }
		else if (self::checkItem('www'.substr($temp2,strpos($temp2,'.')),$compare,$compare2)) { $found= true; }

		if ($found)
		{
			Mage::getConfig()->saveConfig(base64_decode($checkKey),true);
		}	else {
			Mage::getConfig()->deleteConfig(base64_decode($compare2), 'default', 0);
		} 	
		return $found;
	}

	private static function checkItem($temp2,$compare,$compare2)	{	
        if (sha1(sha1(base64_decode($compare)) . $temp2) == Mage::getStoreConfig(base64_decode($compare2))) { return true; }
        else {
        	foreach (Mage::app()->getStores(true) as $store){
        		if(sha1(sha1(base64_decode($compare)) . $temp2) == Mage::getStoreConfig(base64_decode($compare2),$store)) {
        			 return true;
        		}
        	}
       }
        return false;
	}
	
	public static function isEnterpriseEdition() {
		if (!Mage::getConfig()->getNode('modules/Enterprise_Cms')) {
            return false;
        }
        return true;
	}
	
	/**
     * Check is module exists and enabled in global config.
	 * This is only needed for 1.4.0.1 or less, but might aswell have in for all
	 * See Mage_Core_Helper_Abstract for defn
	 * Enter description here ...
	 * @param $moduleName
	 */
	public function isModuleEnabled($moduleName = null,$enabledLocation=null)
    {
   	
        if ($moduleName === null) {
            $moduleName = $this->_getModuleName();
        }

        if (!Mage::getConfig()->getNode('modules/' . $moduleName)) {
            return false;
        }

        $isActive = Mage::getConfig()->getNode('modules/' . $moduleName . '/active');
        $test = (string)$isActive;
        if (!$isActive || !in_array((string)$isActive, array('true', '1'))) {
            return false;
        }
        
        if ($enabledLocation === null) {
        	return true;
        }
        
        if (!Mage::getStoreConfig($enabledLocation)) {
        	return false;
        }
        
        return true;
    }
	
	
	
}