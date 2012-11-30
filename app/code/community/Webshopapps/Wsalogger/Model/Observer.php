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

class Webshopapps_Wsalogger_Model_Observer extends Mage_Core_Model_Abstract
{
	
	/**
	 * event wsalogger_log_mesasge
	 */
	public function logMessage($observer){
	
		if (!Mage::getStoreConfig('wsalogmenu/wsalog/active')) {
    	 	return ;
    	}
    	 
    	$adminLevel = Mage::getStoreConfig('wsalogmenu/wsalog/admin_level');
    	$systemLogLevel = Mage::getStoreConfig('wsalogmenu/wsalog/system_level');
    	$emailLevel = Mage::getStoreConfig('wsalogmenu/wsalog/email_level');
    	
    	$code 			= $observer->getEvent()->getCode();
    	$url 			= $observer->getEvent()->getUrl();
    	$severity 		= $observer->getEvent()->getSeverity();
        $title 			= $observer->getEvent()->getTitle();
        $extension		= $observer->getEvent()->getExtension();
        $description 	= $observer->getEvent()->getDescription();
    	
    	if ($adminLevel>0 && $adminLevel>=$severity) {
    	 	Mage::getModel('wsalogger/log')->parse($severity,$extension,$title,$description,$code,$url);        
		}
    	 
	 	if ($systemLogLevel>0 && $systemLogLevel>=$severity) {
	 		Mage::log(Mage::helper('wsalogger/log')->getSeverities($severity).' '.$code.' '.$url.' - '.$extension.' - '.$title);
	 		if (!is_null($description) && $description!='') {
	 			Mage::log($description);
	 		}
	 	
		}
		
	 	if ($emailLevel>0 && $emailLevel>=$severity) {
    	 	/*  * Loads the html file named 'custom_email_template1.html' from 
    	 	 * app/locale/en_US/template/email/activecodeline_custom_email1.html  */
    	 	$emailTemplate  = Mage::getModel('core/email_template')
    	 	                         ->loadDefault('log_email_template');    
    	 	                         
    	 	$emailTemplate->setSenderName(Mage::getStoreConfig('wsalogmenu/wsalog/sender_email_name'));
			$emailTemplate->setSenderEmail(Mage::getStoreConfig('wsalogmenu/wsalog/sender_email'));
			$emailTemplate->setTemplateSubject(Mage::getStoreConfig('wsalogmenu/wsalog/email_subject'));
				 	    	
			if (is_array($description) || is_object($description)) {
        		$description = print_r($description, true);
        	}
    		$description = htmlentities($description);
			
    		//Create an array of variables to assign to template 
    		//TODO add severity
    	 	$emailTemplateVariables 				= array(); 
    	 	$emailTemplateVariables['title'] 		= $title; 
    	 	$emailTemplateVariables['severity'] 	= $severity; 
    	 	$emailTemplateVariables['extension'] 	= $extension; 
    	 	$emailTemplateVariables['description'] 	= $description;   
    	 	$emailTemplateVariables['code'] 		= $code;   
    	 	$emailTemplateVariables['url']		 	= $url;   
    	 	
	 
    	 	$processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);   
    	 	/*  * Or you can send the email directly,  * note getProcessedTemplate is called inside send()  */
    	 	
    	 	$emailTemplate->send(Mage::getStoreConfig('wsalogmenu/wsalog/contact_email'),'', $emailTemplateVariables);
	 		       
		}	 
	}
}