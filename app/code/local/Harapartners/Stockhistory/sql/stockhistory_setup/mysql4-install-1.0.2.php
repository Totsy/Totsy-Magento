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


CREATE TABLE {$this->getTable('stockhistory/vendor')}(
	`id`		 		int(10) unsigned NOT NULL auto_increment,
	`vendor_name` 		varchar(50) NOT NULL default '',
	`vendor_sku`		varchar(50) NOT NULL default '',
	`contact_person`	varchar(50) NOT NULL default '',
	`email`				varchar(30) NOT NULL default '',
	`phone`				varchar(30) NOT NULL default '',
	`comment`			text NOT NULL default '',
	`created_at`		datetime default NULL,
	`updated_at` 		datetime default NULL,
	`store_id`			smallint(5) unsigned DEFAULT 0,
	
	PRIMARY KEY	(`id`),
	UNIQUE KEY `FK_STOCKHISTORY_VENDOR_SKU` (`vendor_sku`),
	
  CONSTRAINT `FK_STOCKHISTORY_VENDOR_STORE` FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core_store')} (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Vendor';



CREATE TABLE {$this->getTable('stockhistory/purchaseorder')}(
	`id`		 		int(10) unsigned NOT NULL auto_increment,
	`vendor_id` 		int(10) unsigned default NULL,
	`name`				varchar(50) NOT NULL default '',
	`comment`			text NOT NULL default '',
	`created_at`		datetime default NULL,
	`updated_at` 		datetime default NULL,
	`store_id`			smallint(5) unsigned DEFAULT 0,
	
	PRIMARY KEY	(`id`),
	KEY `FK_STOCKHISTORY_PURCHASEORDER_VENDOR` (`vendor_id`),
	
  CONSTRAINT `FK_STOCKHISTORY_PURCHASEORDER_VENDOR` FOREIGN KEY (`vendor_id`) REFERENCES {$this->getTable('stockhistory/vendor')} (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_STOCKHISTORY_PURCHASEORDER_STORE` FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core_store')} (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Purchase Order';



CREATE TABLE {$this->getTable('stockhistory/report')}(
	`id`		 	int(10) unsigned NOT NULL auto_increment,
	`vendor_id` 	int(10) unsigned default NULL,
	`po_id`			int(10) unsigned default NULL,
	`product_id`	int(10)	unsigned default NULL,
	`category_id`	int(10) unsigned default NULL,
	`product_sku`	varchar(50) NOT NULL default '',
	`vendor_sku`	varchar(30)	NOT NULL default '',
	`unit_cost`		smallint(10) NOT NULL default '0',
	`qty_delta`			smallint(10) NOT NULL default '0',
	`created_at`	datetime default NULL,
	`updated_at` 	datetime default NULL,
	`action_type`	varchar(20) default NULL,
	`comment`		text NOT NULL default '',
	`store_id`			smallint(5) unsigned DEFAULT 0,
	PRIMARY KEY	(`id`),
	KEY `FK_STOCKHISTORY_REPORT_ITEM` (`product_id`),
	KEY `FK_STOCKHISTORY_REPORT_VENDOR` (`vendor_id`),
	KEY `FK_STOCKHISTORY_REPORT_PO` (`po_id`),
  CONSTRAINT `FK_STOCKHISTORY_REPORT_ITEM` FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog/product')} (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_STOCKHISTORY_REPORT_STORE` FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core_store')} (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_STOCKHISTORY_REPORT_VENDOR` FOREIGN KEY (`vendor_id`) REFERENCES {$this->getTable('stockhistory/vendor')} (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_STOCKHISTORY_REPORT_PO` FOREIGN KEY (`po_id`) REFERENCES {$this->getTable('stockhistory/purchaseorder')} (`id`) ON DELETE SET NULL ON UPDATE CASCADE
  
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stock Report'




");


$installer->endSetup();

?>