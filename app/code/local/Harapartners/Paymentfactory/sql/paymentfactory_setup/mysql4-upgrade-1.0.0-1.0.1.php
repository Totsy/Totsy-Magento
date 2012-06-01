<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("
ALTER TABLE {$this->getTable('paymentfactory/profile')}
ADD address_id VARCHAR(255) NULL;
");
 
$installer->endSetup();