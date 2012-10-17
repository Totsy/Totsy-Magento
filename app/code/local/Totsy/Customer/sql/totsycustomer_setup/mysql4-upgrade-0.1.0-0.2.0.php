<?php
$installer = $this;
$installer->startSetup();

$installer->run("
    ALTER TABLE `paymentfactory_profile` MODIFY `address_id` int(10) unsigned NOT NULL;

    SET FOREIGN_KEY_CHECKS = 0;
    ALTER TABLE paymentfactory_profile
    ADD FOREIGN KEY (`address_id`)
    REFERENCES customer_address_entity(`entity_id`);
    SET FOREIGN_KEY_CHECKS = 1;
");

$installer->endSetup();
