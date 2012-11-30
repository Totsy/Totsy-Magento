<?php
/**
 * WebShopApps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    WebShopApps
 * @package     WebShopApps WsaLogger
 * @copyright   Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Webshopapps_Wsalogger_Helper_Data extends Mage_Core_Helper_Abstract
{

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
			else $eeVersion = 8;  // default to this if unsure, changed to assume is 1.4.1+
		}
		return $eeVersion;
	}
	
	
	public static function isEnterpriseEdition() {
			if (!Mage::getConfig()->getNode('modules/Enterprise_Cms')) {
	            return false;
	        }
	        return true;
	}
	
public function isDebug($moduleName) {
		
		$path = 'wsalogmenu/modules_disable_logger/'.$moduleName; 
		return Mage::getStoreConfig($path) ? true : false;
		
	}
	
}