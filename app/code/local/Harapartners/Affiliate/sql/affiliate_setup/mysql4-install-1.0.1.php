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

-- DROP TABLE IF EXISTS {$this->getTable('affiliate/record')};
CREATE TABLE {$this->getTable('affiliate/record')} (
  `affiliate_id` int(10) unsigned NOT NULL auto_increment,
  `affiliate_code` varchar(255) NOT NULL default '',
  `affiliate_name` varchar(255) NOT NULL default '', 
  `status` smallint(5) unsigned default 0,
  `type` smallint(5) unsigned default 0,
  `tracking_code` text NOT NULL default '',
  `sub_affiliate_code` text NOT NULL default '',
  `comment` text NOT NULL default '',
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  `store_id` smallint(5) unsigned default '0',
  PRIMARY KEY  (`affiliate_id`),
  UNIQUE KEY `UNIQUE_KEY_AFFILIATE_CODE` (`affiliate_code`),
  KEY `FK_AFFILIATE_RECORD_STORE` (`store_id`),
  CONSTRAINT `FK_AFFILIATE_RECORD_STORE` FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core_store')} (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Affiliate Record';

  ");
 
$installer->endSetup();