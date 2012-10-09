<?php
<<<<<<< HEAD
/**
 * @category    Totsy
 * @package     Totsy
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

=======
>>>>>>> 2d51cfbf841ee59c60830d650424fd08c75bd248
$installer = $this;
$installer->startSetup();

$installer->run("
<<<<<<< HEAD
    -- DROP TABLE IF EXISTS {$this->getTable('totsycustomer/zipCodeInfo')};
    CREATE TABLE {$this->getTable('totsycustomer/zipCodeInfo')} (
        `customer_zip_code_info_id` int(10) unsigned NOT NULL auto_increment,
        `zip` char(5) DEFAULT NULL COMMENT 'ZIP Code',
        `city` varchar(255) DEFAULT NULL COMMENT 'City',
        `state` char(2) DEFAULT NULL COMMENT 'State',
        `type` varchar(32) DEFAULT 'STANDARD' COMMENT 'Zip Code Type',
        PRIMARY KEY (`customer_zip_code_info_id`),
        INDEX (`zip`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ZIP Code Information';
");

$installer->endSetup();
=======
    SET FOREIGN_KEY_CHECKS = 0;
    ALTER TABLE paymentfactory_profile
    ADD FOREIGN KEY (`address_id`)
    REFERENCES customer_address_entity(`entity_id`);
    SET FOREIGN_KEY_CHECKS = 1;
");

$installer->endSetup();
>>>>>>> 2d51cfbf841ee59c60830d650424fd08c75bd248
