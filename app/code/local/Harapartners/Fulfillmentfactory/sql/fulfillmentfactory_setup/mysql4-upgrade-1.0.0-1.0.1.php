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
    -- DROP TABLE IF EXISTS {$this->getTable('fulfillmentfactory/errorlog')};
    CREATE TABLE {$this->getTable('fulfillmentfactory/errorlog')} (
    `entity_id` int(10) unsigned NOT NULL auto_increment COMMENT 'log id',
    `store_id` smallint(5) unsigned DEFAULT NULL COMMENT 'Store Id',
    `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'item_id from sales_flat_order_item',
    `message` varchar(255) DEFAULT NULL COMMENT 'error message',
    `created_at` timestamp NULL DEFAULT NULL COMMENT 'Created At',
    `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Updated At',
    PRIMARY KEY (`entity_id`),
    CONSTRAINT `FK_FULFILLMENT_ERRORLOG_ORDER_ID_SALES_FLAT_ORDER_ENTITY_ID` FOREIGN KEY (`order_id`) REFERENCES `sales_flat_order` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_FULFILLMENT_ERRORLOG_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Fulfillment Factory Errorlog';
");
    
$installer->endSetup();

?>