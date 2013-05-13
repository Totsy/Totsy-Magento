<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE `{$this->getTable('sales/quote_address_item')}`
    ADD `category_id` int(10) DEFAULT NULL COMMENT 'Category ID' AFTER `name`,
    ADD `category_name` varchar(78) DEFAULT NULL COMMENT 'Category Name' AFTER `category_id`,
    ADD KEY `IDX_SALES_FLAT_QUOTE_ADDRESS_ITEM_CATEGORY_ID` (`category_id`)");

$installer->endSetup();
