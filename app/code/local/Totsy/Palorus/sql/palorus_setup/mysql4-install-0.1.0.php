<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("
ALTER TABLE {$installer->getTable('palorus/vault')}
ADD address_id INT(20) NULL;
");

$installer->endSetup();