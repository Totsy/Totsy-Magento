<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('promotionfactory/emailcoupon')};
CREATE TABLE {$this->getTable('promotionfactory/emailcoupon')} (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `rule_id` int(10) ,
  `code` varchar(50) default NULL,
  `name` varchar(50) NOT NULL default '',
  `email` varchar(255) default NULL,
  `store_id` smallint(5) unsigned DEFAULT NULL,
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  `used_count` int(10) default 0,
  PRIMARY KEY  (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='EMAIL COUPON';

-- DROP TABLE IF EXISTS {$this->getTable('promotionfactory/groupcoupon')};
CREATE TABLE {$this->getTable('promotionfactory/groupcoupon')} (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `rule_id` int(10) unsigned NOT NULL,
  `pseudo_code` varchar(50) NOT NULL default '',
  `code` varchar(50) default NULL,
  `store_id` smallint(5) unsigned DEFAULT NULL,
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  `used_count` int(10) default 0,
  PRIMARY KEY  (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='GROUP COUPON';


  ");
 
$installer->endSetup();