<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE `{$this->getTable('sales/order_grid')}`
    ADD `customer_email` varchar(255) DEFAULT NULL COMMENT 'Customer Email' after `order_currency_code`,
    ADD KEY `IDX_SALES_FLAT_ORDER_GRID_CUSTOMER_EMAIL` (`customer_email`)");
$installer->run("
UPDATE `{$this->getTable('sales/order_grid')}` sfog
JOIN `{$this->getTable('sales/order')}` sfo on sfo.entity_id=sfog.entity_id
SET sfog.customer_email = sfo.customer_email
");

$installer->endSetup();
