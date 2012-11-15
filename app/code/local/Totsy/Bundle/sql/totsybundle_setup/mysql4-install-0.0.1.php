<?php

$installer = $this;

$installer->startSetup();

$installer->run("
TRUNCATE `catalog_product_link_attribute`;
INSERT INTO `catalog_product_link_attribute` VALUES ('1', '1', 'position', 'int');
INSERT INTO `catalog_product_link_attribute` VALUES ('2', '3', 'position', 'int');
INSERT INTO `catalog_product_link_attribute` VALUES ('3', '3', 'qty', 'decimal');
INSERT INTO `catalog_product_link_attribute` VALUES ('4', '4', 'position', 'int');
INSERT INTO `catalog_product_link_attribute` VALUES ('5', '5', 'position', 'int');

TRUNCATE `catalog_product_link_type`;
INSERT INTO `catalog_product_link_type` VALUES ('1', 'relation');
INSERT INTO `catalog_product_link_type` VALUES ('2', 'bundle');
INSERT INTO `catalog_product_link_type` VALUES ('3', 'super');
INSERT INTO `catalog_product_link_type` VALUES ('4', 'up_sell');
INSERT INTO `catalog_product_link_type` VALUES ('5', 'cross_sell');
");

$installer->endSetup();
