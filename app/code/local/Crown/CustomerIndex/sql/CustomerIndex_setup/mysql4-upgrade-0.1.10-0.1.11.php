<?php
$installer = $this;

$installer->startSetup ();

// Update Table Schema
$query = "
ALTER TABLE `{$this->getTable('CustomerIndex/CustomerIndex')}`
ADD `club_created_at` timestamp NULL DEFAULT NULL COMMENT 'TotsyPLUS Since',
ADD INDEX `IDX_CUSTOMER_ENTITY_FLAT_CLUB_CREATED_AT` (`club_created_at`);
";
$installer->run ($query);

$installer->endSetup ();