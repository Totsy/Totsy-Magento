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

-- DROP TABLE IF EXISTS {$this->getTable('stockhistory/vendor')};
CREATE TABLE {$this->getTable('stockhistory/vendor')}(
    `id`                 int(10) unsigned NOT NULL auto_increment,
    `vendor_name`         varchar(255) NOT NULL default '',
    `vendor_code`        varchar(255) NOT NULL default '',
    `vendor_type`        smallint(5) unsigned DEFAULT 0,
    `contact_person`    varchar(255) NOT NULL default '',
    `email_list`        varchar(255) NOT NULL default '',
    `phone`                varchar(255) NOT NULL default '',
    `address`            text NOT NULL default '',
    `parent_id`            int(10) unsigned default NULL,
    `comment`            text NOT NULL default '',
    `status`            smallint(5) unsigned default NULL,
    `created_at`        datetime default NULL,
    `updated_at`         datetime default NULL,
    `store_id`            smallint(5) unsigned DEFAULT 0,
    
    PRIMARY KEY    (`id`),
    UNIQUE KEY `FK_STOCKHISTORY_VENDOR_CODE` (`vendor_code`),
    KEY `FK_STOCKHISTORY_VENDOR_STORE` (`store_id`),
      CONSTRAINT `FK_STOCKHISTORY_VENDOR_STORE` FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core_store')} (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Vendor';


-- DROP TABLE IF EXISTS {$this->getTable('stockhistory/purchaseorder')};
CREATE TABLE {$this->getTable('stockhistory/purchaseorder')}(
    `id`                 int(10) unsigned NOT NULL auto_increment,
    `vendor_id`         int(10) unsigned default NULL,
    `name`                varchar(255) NOT NULL default '',
    `comment`            text NOT NULL default '',
    `status`            smallint(5) unsigned default NULL,
    `created_at`        datetime default NULL,
    `updated_at`         datetime default NULL,
    `store_id`            smallint(5) unsigned DEFAULT 0,
    
    PRIMARY KEY    (`id`),
    KEY `FK_STOCKHISTORY_PURCHASEORDER_VENDOR` (`vendor_id`),
    KEY `FK_STOCKHISTORY_PURCHASEORDER_STORE` (`store_id`),
    CONSTRAINT `FK_STOCKHISTORY_PURCHASEORDER_VENDOR` FOREIGN KEY (`vendor_id`) REFERENCES {$this->getTable('stockhistory/vendor')} (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_STOCKHISTORY_PURCHASEORDER_STORE` FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core_store')} (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Purchase Order';


-- DROP TABLE IF EXISTS {$this->getTable('stockhistory/transaction')};
CREATE TABLE {$this->getTable('stockhistory/transaction')}(
    `id`             int(10) unsigned NOT NULL auto_increment,
    `vendor_id`     int(10) unsigned default NULL,
    `vendor_code`    varchar(255) NOT NULL default '',
    `po_id`            int(10) unsigned default NULL,
    `category_id`    int(10) unsigned default NULL,
    `product_id`    int(10)    unsigned default NULL,
    `product_sku`    varchar(255) NOT NULL default '',
    `unit_cost`        decimal(12,4) NOT NULL default 0.0000,
    `qty_delta`        int(10) NOT NULL default 0,
    `action_type`    smallint(5) unsigned NOT NULL default 0,
    `comment`        text NOT NULL default '',
    `status`        smallint(5) unsigned default NULL,
    `created_at`    datetime default NULL,
    `updated_at`     datetime default NULL,
    `store_id`        smallint(5) unsigned DEFAULT 0,
    PRIMARY KEY    (`id`),
    KEY `FK_STOCKHISTORY_TRANSACTION_ITEM` (`product_id`),
    KEY `FK_STOCKHISTORY_TRANSACTION_VENDOR` (`vendor_id`),
    KEY `FK_STOCKHISTORY_TRANSACTION_PO` (`po_id`),
    KEY `FK_STOCKHISTORY_TRANSACTION_STORE` (`store_id`),
    
    CONSTRAINT `FK_STOCKHISTORY_TRANSACTION_ITEM` FOREIGN KEY (`product_id`) REFERENCES {$this->getTable('catalog/product')} (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_STOCKHISTORY_TRANSACTION_STORE` FOREIGN KEY (`store_id`) REFERENCES {$this->getTable('core_store')} (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_STOCKHISTORY_TRANSACTION_VENDOR` FOREIGN KEY (`vendor_id`) REFERENCES {$this->getTable('stockhistory/vendor')} (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_STOCKHISTORY_TRANSACTION_PO` FOREIGN KEY (`po_id`) REFERENCES {$this->getTable('stockhistory/purchaseorder')} (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Stock Report';


");

$installer->endSetup();

?>