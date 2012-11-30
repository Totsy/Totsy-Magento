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

class Webshopapps_Wsalogger_Helper_Log extends Mage_Core_Helper_Abstract
{
	
	const SEVERITY_CRITICAL = 1;
    const SEVERITY_MAJOR    = 2;
    const SEVERITY_MINOR    = 3;
    const SEVERITY_NOTICE   = 4;
    const SEVERITY_NONE     = -1;
    
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $severity - CRITIAL,MAJOR,MINOR,NOTICE - 1-4
	 * @param unknown_type $title
	 * @param unknown_type $description
	 * @param unknown_type $url
	 */
	
	public static function postDebug($extension,$title,$description,$debug=true,$code=0,$url='') {
		
		if (!Mage::getStoreConfig('wsalogmenu/wsalog/active') || !$debug) {
    		return ;
    	}  
        
       Mage::dispatchEvent('wsalogger_log_mesasge', 
       					array('severity'=>self::SEVERITY_NOTICE,
       						  	'title' => $title,
       						  	'extension' => $extension,
       						 	'description' => $description,
       						  	'code'			=> $code,
       							'url'			=> $url
       							));
	}
	

	public static function postInfo($extension,$title,$description,$debug=true,$code=0,$url='') {
		
		if (!Mage::getStoreConfig('wsalogmenu/wsalog/active')) {
    		return ;
    	}
    	  

        Mage::dispatchEvent('wsalogger_log_mesasge', 
       					array('severity'=>self::SEVERITY_MINOR,
       						  	'extension' => $extension,
       							'title' => $title,
       						  	'description' => $description,
       					       	'code'			=> $code,
       							'url'			=> $url       						 	     					
       							));
	}
	
	
	public static function postWarning($extension,$title,$description,$debug=true,$code=0,$url='') {
		
		if (!Mage::getStoreConfig('wsalogmenu/wsalog/active')) {
    		return ;
    	}
		
        Mage::dispatchEvent('wsalogger_log_mesasge', 
       					array('severity'=>self::SEVERITY_MAJOR,
       						  	'title' => $title,
       						  	'extension' => $extension,
       							'description' => $description,
       					       	'code'			=> $code,
       							'url'			=> $url
       							));        
	}
	
	public static function postCritical($extension,$title,$description,$debug=true,$code=0,$url='') {
		
		if (!Mage::getStoreConfig('wsalogmenu/wsalog/active')) {
    		return ;
    	}
    	      
       	Mage::dispatchEvent('wsalogger_log_mesasge', 
       					array('severity'=>self::SEVERITY_CRITICAL,
       						  	'title' => $title,
       						  	'extension' => $extension,
       							'description' => $description,
       					       	'code'			=> $code,
       							'url'			=> $url
       							));              
	}
	
	
 	public function getSeverities($severity = null)
    {
    	
    	return Mage::getSingleton('wsalogger/log')->getSeverities($severity);
       
    }
	
	
}