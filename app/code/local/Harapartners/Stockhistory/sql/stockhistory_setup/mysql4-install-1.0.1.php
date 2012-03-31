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

-- DROP TABLE IF EXISTS {$this->getTable('stockhistory/history')};
CREATE TABLE {$this->getTable('stockhistory/history')}(
	`history_id` 	int(10) unsigned NOT NULL auto_increment,
	`entity_id` 	int(10) unsigned default NULL,
	`product_name`	varchar(50) NOT NULL default '',
	`product_sku`	varchar(50) NOT NULL default '',
	`size`			varchar(30) NOT NULL default '',
	`color`			varchar(30) NOT NULL default '',
	`vendor_sku`	varchar(30)	NOT NULL default '',
	`qty_delta`		int(10) NOT NULL default '0',
	`unit_cost`		int(10)	NOT NULL default '0',
	`total_cost`   	int(10)	NOT NULL default '0',
	`created_at`	datetime default NULL,
	`updated_at` 	datetime default NULL,
	`status`		varchar(20) default NULL,
	`comment`		varchar(100) default '',
	PRIMARY KEY	(`history_id`),
	KEY `FK_STOCKHISTORY_HISTORY_ITEM` (`entity_id`),
  CONSTRAINT `FK_STOCKHISTORY_HISTORY_ITEM` FOREIGN KEY (`entity_id`) REFERENCES {$this->getTable('catalog/product')} (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stock History'

-- DROP TABLE IF EXISTS {$this->getTable('stockhistory/report')};
CREATE TABLE {$this->getTable('stockhistory/report')}(
	`id`		 	int(10) unsigned NOT NULL auto_increment,
	`entity_id` 	int(10) unsigned default NULL,
	`product_name`	varchar(50) NOT NULL default '',
	`product_sku`	varchar(50) NOT NULL default '',
	`vendor_sku`	varchar(30)	NOT NULL default '',
	`qty`			smallint(10) NOT NULL default '0',
	`created_at`	datetime default NULL,
	`updated_at` 	datetime default NULL,
	`status`		varchar(20) default NULL,
	`comment`		varchar(100) default '',
	PRIMARY KEY	(`id`),
	KEY `FK_STOCKHISTORY_REPORT_ITEM` (`entity_id`),
  CONSTRAINT `FK_STOCKHISTORY__ITEM` FOREIGN KEY (`entity_id`) REFERENCES {$this->getTable('catalog/product')} (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stock Report'

-- DROP TABLE IF EXISTS {$this->getTable('stockhistory/report')};
CREATE TABLE {$this->getTable('stockhistory/vendor')}(
	`id`		 	int(10) unsigned NOT NULL auto_increment,
	`entity_id` 	int(10) unsigned default NULL,
	`product_name`	varchar(50) NOT NULL default '',
	`product_sku`	varchar(50) NOT NULL default '',
	`vendor_sku`	varchar(30)	NOT NULL default '',
	`qty`			smallint(10) NOT NULL default '0',
	`created_at`	datetime default NULL,
	`updated_at` 	datetime default NULL,
	`status`		varchar(20) default NULL,
	`comment`		varchar(100) default '',
	PRIMARY KEY	(`id`),
	KEY `FK_STOCKHISTORY_REPORT_ITEM` (`entity_id`),
  CONSTRAINT `FK_STOCKHISTORY__ITEM` FOREIGN KEY (`entity_id`) REFERENCES {$this->getTable('catalog/product')} (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stock Report'
");


$installer->endSetup();

?>