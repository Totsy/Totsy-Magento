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
ALTER TABLE {$this->getTable('stockhistory/purchaseorder')} ADD INDEX (`po_number`);
DROP TABLE IF EXISTS {$this->getTable('fulfillment/receipt')};
CREATE TABLE {$this->getTable('fulfillment/receipt')} (
    `receipt_id` int(10) unsigned NOT NULL auto_increment COMMENT 'This is Magento assigned id',
    `po_number` varchar(255) default '' COMMENT 'This is the po number the receipt is related to',
    `units_received` int(10) unsigned default 0 COMMENT 'This is the total number of units received by warehouse',
    `damaged_units_received` int(10) unsigned default 0 COMMENT 'This is the total number of damage units received from vendor',
    `logistics_provider` varchar(255) default '' COMMENT 'This is the name of the logistics provider from this receipt was retrieved',
    `warehouse_location` varchar(225) default '' COMMENT 'This is the location of the warehouse the goods was received',
    `status`  varchar(10) default '' COMMENT 'This is the status of the PO at the warehouse',
    `po_sent_date` datetime default NULL COMMENT 'This is the date the PO file was sent to the logistics provider',
    `warehouse_arrival_date` datetime default NULL COMMENT 'this is the date the goods arrive at the warehouse',
    `cargo_received_date` datetime default NULL COMMENT 'this is the date the warehouse process and completed stocking the goods',
    `created_date` datetime default NULL COMMENT 'this is the date this receipt was record in the db',
    `updated_date` datetime default NULL COMMENT 'this is the date this receipt was updated in the db',
    PRIMARY KEY (`receipt_id`),

    CONSTRAINT `FK_FULFILLMENT_RECEIPT_PONUMBER` FOREIGN KEY(`po_number`) REFERENCES {$this->getTable('stockhistory/purchaseorder')} (`po_number`) ON DELETE NO ACTION ON UPDATE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Purchase Order Receipt Records';
");

$installer->endSetup();

?>
