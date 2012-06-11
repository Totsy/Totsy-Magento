<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('promotionfactory/virtualproductcoupon')};
CREATE TABLE {$this->getTable('promotionfactory/virtualproductcoupon')} (
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `product_id` int(10) unsigned NOT NULL,
  `code` varchar(50) default NULL,
  `status` smallint(5) unsigned NOT NULL default '0',
  `customer_id` int(10) unsigned default NULL,
  `order_id` int(10) unsigned default NULL,
  `order_item_id` int(10) unsigned default NULL,
  `store_id` smallint(5) unsigned DEFAULT NULL,
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  PRIMARY KEY  (`entity_id`),
  UNIQUE PROMO_CODE (`code`),
  KEY (`product_id`),
  CONSTRAINT `FK_PAYMENTFACTORY_PRODUCT_ID` FOREIGN KEY (`product_id`) REFERENCES {$installer->getTable('catalog/product')} (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='VIRTUAL PRODUCT COUPON';

  ");
 
$installer->endSetup();