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

-- DROP TABLE IF EXISTS {$this->getTable('customertracking/record')};
CREATE TABLE {$this->getTable('customertracking/record')} (
  `customertracking_id` int(10) unsigned NOT NULL auto_increment,
  `affiliate_id` int(10) unsigned default NULL,
  `affiliate_code` varchar(255) NOT NULL default '',
  `sub_affiliate_code` varchar(255) NOT NULL default '',
  `customer_id` int(10) unsigned default NULL,  
  `customer_email` varchar(255) NOT NULL default '',
  `status` smallint(5) unsigned default NULL,
  `registration_param` text NOT NULL default '',
  `login_count` int(10) unsigned NOT NULL default '0',
  `page_view_count` int(10) unsigned NOT NULL default '0',
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  `store_id` smallint(5) unsigned default '0',
  PRIMARY KEY  (`customertracking_id`),
  KEY `FK_CUSTOMERTRACKING_RECORD_AFFILIATE` (`affiliate_id`),
  KEY `FK_CUSTOMERTRACKING_RECORD_CUSTOMER` (`customer_id`),
  KEY `FK_CUSTOMERTRACKING_RECORD_STORE` (`store_id`),
  CONSTRAINT `FK_CUSTOMERTRACKING_RECORD_AFFILIATE` FOREIGN KEY (`affiliate_id`) REFERENCES {$this->getTable('affiliate/record')} (`affiliate_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_CUSTOMERTRACKING_RECORD_CUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES {$this->getTable('customer_entity')} (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_CUSTOMERTRACKING_RECORD_STORE` FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core_store')} (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Customer Tracking Record';

  ");
 
$installer->endSetup();

?>