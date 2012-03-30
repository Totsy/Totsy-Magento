<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('paymentfactory/profile')};
CREATE TABLE {$this->getTable('paymentfactory/profile')} (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `customer_email` varchar(255) NOT NULL,
  `customer_id` int(40) NOT NULL,
  `subscription_id` varchar(255) NOT NULL,
  `cc_number_hash` varchar(255) NOT NULL,
  `nickname` varchar(255) NOT NULL,
  `last4no` varchar(10) default '',
  `expire_year` varchar(10) default '',
  `expire_month` varchar(10) default '',
  `card_type` varchar(10) default '',
  `is_default` int(1) default 0,
  `store_id` smallint(5) unsigned DEFAULT NULL,
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  PRIMARY KEY (`entity_id`),
  UNIQUE KEY (`subscription_id`),
  UNIQUE KEY (`cc_number_hash`),
KEY `FK_PAYMENTFACTORY_PROFILE_CUSTOMER` (`customer_id`),
CONSTRAINT `FK_PAYMENTFACTORY_PROFILE_CUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES {$installer->getTable('customer/entity')} (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='PROFILE';
  ");
 
$installer->endSetup();