<?php

$installer = $this;

$installer->startSetup();

$installer->run("
INSERT INTO `catalog_product_link_type` VALUES ('2', 'bundle');
");

$installer->endSetup();
