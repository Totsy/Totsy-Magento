<?php
$installer = $this;

$installer->startSetup ();

// Update Table Schema
$query = "
ALTER TABLE `{$this->getTable('CustomerIndex/CustomerIndex')}`
ADD `firstname` varchar(255) DEFAULT NULL COMMENT 'First Name',
ADD `lastname` varchar(255) DEFAULT NULL COMMENT 'Last Name',
ADD INDEX `IDX_CUSTOMER_ENTITY_FLAT_FIRSTNAME_LASTNAME` (`firstname`,`lastname`),
ADD INDEX `IDX_CUSTOMER_ENTITY_FLAT_FIRSTNAME` (`firstname`),
ADD INDEX `IDX_CUSTOMER_ENTITY_FLAT_LASTNAME` (`lastname`)
";
$installer->run ($query);

$installer->endSetup ();
