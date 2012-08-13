<?php
$installer = $this;

$installer->startSetup ();

// Create Table Schema
$query = "
DROP TABLE IF EXISTS `{$this->getTable('CustomerIndex/CustomerIndex')}`;
CREATE TABLE `{$this->getTable('CustomerIndex/CustomerIndex')}` (
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Cusomer ID',
  `customer_name` varchar(255) DEFAULT NULL COMMENT 'Concat Name',
  `email` varchar(255) DEFAULT '' COMMENT 'Email',
  `group_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Group',
  `billing_telephone` varchar(255) DEFAULT NULL COMMENT 'Telephone',
  `billing_postcode` varchar(255) DEFAULT NULL COMMENT 'ZIP',
  `billing_country_id` varchar(255) DEFAULT NULL COMMENT 'Country',
  `billing_region` varchar(255) DEFAULT NULL COMMENT 'State/Province',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Customer Since',
  `website_id` smallint(5) DEFAULT NULL COMMENT 'Website',
  `store_id` smallint(5) unsigned DEFAULT '0' COMMENT 'Store ID',
  PRIMARY KEY (`entity_id`),
  KEY `IDX_CUSTOMER_ENTITY_FLAT_EMAIL_WEBSITE_ID` (`email`,`website_id`),
  KEY `IDX_CUSTOMER_ENTITY_FLAT_WEBSITE_ID` (`website_id`),
  KEY `IDX_CUSTOMER_ENTITY_FLAT_EMAIL` (`email`),
  KEY `IDX_CUSTOMER_ENTITY_FLAT_NAME` (`customer_name`),
  KEY `IDX_CUSTOMER_ENTITY_FLAT_GROUP_ID` (`group_id`),
  KEY `IDX_CUSTOMER_ENTITY_FLAT_BILLING_TELEPHONE` (`billing_telephone`),
  KEY `IDX_CUSTOMER_ENTITY_FLAT_BILLING_POSTCODE` (`billing_postcode`),
  KEY `IDX_CUSTOMER_ENTITY_FLAT_BILLING_COUNTRY_ID` (`billing_country_id`),
  KEY `IDX_CUSTOMER_ENTITY_FLAT_BILLING_REGION` (`billing_region`),
  KEY `IDX_CUSTOMER_ENTITY_FLAT_CREATED_AT` (`created_at`),
  CONSTRAINT `FK_CUSTOMER_ENTITY_FLAT_ENTITY_ID_CUSTOMER_ENTITY_ENTITY_ID` FOREIGN KEY (`entity_id`) REFERENCES `{$this->getTable('customer/entity')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";
$installer->run ($query);

$installer->endSetup ();
