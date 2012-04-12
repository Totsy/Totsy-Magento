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

ALTER TABLE {$this->getTable('stockhistory/purchaseorder')}
	ADD `vendor_code` varchar(255) NOT NULL default '' AFTER `vendor_id`,
	ADD `category_id` int(10) unsigned default NULL AFTER `name`,
    ADD KEY `FK_STOCKHISTORY_PURCHASEORDER_CATEGORY` (`category_id`),
	ADD CONSTRAINT `FK_STOCKHISTORY_PURCHASEORDER_CATEGORY` FOREIGN KEY (`category_id`) REFERENCES {$this->getTable('catalog_category_entity')} (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE;


ALTER TABLE {$this->getTable('stockhistory/transaction')}
	ADD KEY `FK_STOCKHISTORY_TRANSACTION_CATEGORY` (`category_id`),
	ADD CONSTRAINT `FK_STOCKHISTORY_TRANSACTION_CATEGORY` FOREIGN KEY (`category_id`) REFERENCES {$this->getTable('catalog_category_entity')} (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE;

");

$installer->endSetup();
