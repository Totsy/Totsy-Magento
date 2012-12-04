<?php
/* WSA Common
 *
 * @category   Webshopapps
 * @package    Webshopapps_Wsacommon
 * @copyright  Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license    http://www.webshopapps.com/license/license.txt - Commercial license
 */

/**
 * NOTE: This is deprecated. Please use Wsa Logger now instead.
 * @author Karen Baker
 *
 */
class Webshopapps_Wsacommon_Helper_Log extends Mage_Core_Helper_Abstract
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
	
	public static function postNotice($extension,$title,$description,$debug=true,$code=0,$url='') {
		
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
	

	public static function postMinor($extension,$title,$description,$debug=true,$code=0,$url='') {
		
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
	
	
	public static function postMajor($extension,$title,$description,$debug=true,$code=0,$url='') {
		
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
        $severities = array(
            self::SEVERITY_CRITICAL => Mage::helper('adminnotification')->__('Critical'),
            self::SEVERITY_MAJOR    => Mage::helper('adminnotification')->__('Major'),
            self::SEVERITY_MINOR    => Mage::helper('adminnotification')->__('Minor'),
            self::SEVERITY_NOTICE   => Mage::helper('adminnotification')->__('Notice'),
            self::SEVERITY_NONE     => Mage::helper('adminnotification')->__('None'),
        );

        if (!is_null($severity)) {
            if (isset($severities[$severity])) {
                return $severities[$severity];
            }
            return null;
        }

        return $severities;
    }
	
	
}