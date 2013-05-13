<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE `{$this->getTable('sales/quote_item')}`
    ADD `category_id` int(10) DEFAULT NULL COMMENT 'Category ID' AFTER `name`,
    ADD `category_name` varchar(78) DEFAULT NULL COMMENT 'Category Name' AFTER `category_id`,
    ADD KEY `IDX_SALES_FLAT_QUOTE_ITEM_CATEGORY_ID` (`category_id`)");
$installer->run("ALTER TABLE `{$this->getTable('sales/order_item')}`
    ADD `category_id` int(10) DEFAULT NULL COMMENT 'Category ID' AFTER `name`,
    ADD `category_name` varchar(78) DEFAULT NULL COMMENT 'Category Name' AFTER `category_id`,
    ADD KEY `IDX_SALES_FLAT_ORDER_ITEM_CATEGORY_ID` (`category_id`)");
$installer->run("ALTER TABLE `{$this->getTable('sales/order')}`
    ADD `estimated_ship_date` DATE DEFAULT NULL COMMENT 'Estimated Shipping Date'");

$installer->endSetup();
