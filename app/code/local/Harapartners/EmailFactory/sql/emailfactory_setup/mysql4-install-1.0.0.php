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

$installer = $this;
$installer->startSetup();

$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable('emailfactory_record')}`;
	CREATE TABLE `{$this->getTable('emailfactory_record')}` (
	  `emailfactory_record_id` int(7) unsigned NOT NULL auto_increment,
	  `customer_email` varchar(150) character set latin1 collate latin1_general_ci NOT NULL default '',
	  `send_id` varchar(30) character set latin1 collate latin1_general_ci NOT NULL default '',
	  `sailthru_email_deliver_status` varchar(30) character set latin1 collate latin1_general_ci NOT NULL default '',
	  `sailthru_api_status` int(3) NOT NULL default '0',  
	  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  	  `updated_at` datetime default NULL,
	  `error_message` text,
	  PRIMARY KEY  (`emailfactory_record_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Sailthru email status';
");

$installer->endSetup();
