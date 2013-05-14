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
    CREATE TABLE {$this->getTable('fulfillmentfactory/itemqueue_archive')} (
        `itemqueue_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'item queue id',
        `order_item_id` int(10) unsigned DEFAULT NULL COMMENT 'item_id from sales_flat_order_item',
        `original_quote_item_id` int(10) unsigned DEFAULT NULL COMMENT 'Original Quote Item Id',
        `order_id` int(10) unsigned DEFAULT NULL COMMENT 'item_id from sales_flat_order_item',
        `order_increment_id` varchar(50) DEFAULT NULL COMMENT 'Increment Id from sales_flat_order',
        `store_id` smallint(5) unsigned DEFAULT NULL COMMENT 'Store Id',
        `product_id` int(10) unsigned DEFAULT NULL COMMENT 'Product Id',
        `sku` varchar(255) DEFAULT NULL COMMENT 'Sku',
        `name` varchar(255) DEFAULT NULL COMMENT 'Name',
        `qty_backordered` decimal(12,4) DEFAULT '0.0000' COMMENT 'Qty Backordered',
        `qty_canceled` decimal(12,4) DEFAULT '0.0000' COMMENT 'Qty Canceled',
        `qty_invoiced` decimal(12,4) DEFAULT '0.0000' COMMENT 'Qty Invoiced',
        `qty_ordered` decimal(12,4) DEFAULT '0.0000' COMMENT 'Qty Ordered',
        `qty_refunded` decimal(12,4) DEFAULT '0.0000' COMMENT 'Qty Refunded',
        `qty_shipped` decimal(12,4) DEFAULT '0.0000' COMMENT 'Qty Shipped',
        `fulfill_count` decimal(12,4) DEFAULT '0.0000' COMMENT 'Qty Shipped',
        `status` smallint(5) unsigned DEFAULT NULL COMMENT 'Status for item queue',
        `created_at` timestamp NULL DEFAULT NULL COMMENT 'Created At',
        `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Updated At',
        PRIMARY KEY (`itemqueue_id`),
        UNIQUE KEY `UNQ_FULFILLMENT_ITEMQUEUE_ORDER_ITEM_ID` (`order_item_id`),
        KEY `FK_FULFILLMENT_ITEMQUEUE_ID_SALES_FLAT_ORDER_ENTITY_ID` (`order_id`),
        KEY `FK_FULFILLMENT_ITEMQUEUE_STORE_ID_CORE_STORE_STORE_ID` (`store_id`),
        KEY `sku_status` (`sku`(12),`status`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1476472 DEFAULT CHARSET=utf8 COMMENT='Fulfillment Factory ItemQueue Archive'
");

$installer->endSetup();
