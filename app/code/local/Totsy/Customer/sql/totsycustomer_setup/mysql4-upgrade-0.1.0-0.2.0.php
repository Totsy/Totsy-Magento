<?php

$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE {$this->getTable('totsycustomer/autoregistration')} (
      `customer_autoregistration_id` INT(10) UNSIGNED NOT NULL auto_increment,
      `store_id` SMALLINT(5) UNSIGNED DEFAULT 1,
      `email` VARCHAR(255) NOT NULL DEFAULT '',
      `token` VARCHAR(255) NOT NULL DEFAULT '',
      `created_at` DATETIME DEFAULT NULL,
      `registered_at` DATETIME DEFAULT NULL,
      `customer_id` INT(10) UNSIGNED DEFAULT NULL,
      PRIMARY KEY  (`customer_autoregistration_id`),
      UNIQUE KEY `UNIQUE_KEY_EMAIL` (`email`),
      INDEX (`store_id`),
      INDEX (`customer_id`),
      CONSTRAINT `FK_AUTOREGISTRATION_CUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE,
      CONSTRAINT `FK_AUTOREGISTRATION_STORE` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Customer Auto-Registrations';
");

$installer->endSetup();