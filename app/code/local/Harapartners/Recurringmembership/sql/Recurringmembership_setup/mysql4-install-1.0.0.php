<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('recurringmembership/profile')};
CREATE TABLE {$this->getTable('recurringmembership/profile')} (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `cust_email` varchar(255) NOT NULL,
  `cust_id` int(40) NOT NULL,
  `cybersource_subid` varchar(255) NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) NOT NULL,
  `failed_count` int(10) NOT NULL,
  `store_id` smallint(5) unsigned DEFAULT NULL,
  `status` smallint(5) unsigned NOT NULL default '0',
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  PRIMARY KEY  (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PROFILE';


  ");
 
$installer->endSetup();