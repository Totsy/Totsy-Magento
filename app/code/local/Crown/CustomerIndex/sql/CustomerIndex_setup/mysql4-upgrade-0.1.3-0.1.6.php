<?php
$installer = $this;

$installer->startSetup ();

// Update Table Schema
$query = "
ALTER TABLE `{$this->getTable('CustomerIndex/CustomerIndex')}`
ADD INDEX `IDX_CUSTOMER_ENTITY_FLAT_STORE_ID` (`store_id`);
";
$installer->run ($query);

$installer->endSetup ();
