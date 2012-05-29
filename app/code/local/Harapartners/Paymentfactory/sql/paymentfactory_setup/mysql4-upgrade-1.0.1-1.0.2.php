<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("
ALTER TABLE {$this->getTable('paymentfactory/profile')}
ADD saved_by_customer SMALLINT(5) NULL;
UPDATE {$this->getTable('paymentfactory/profile')}
SET saved_by_customer = 1;
");
 
$installer->endSetup();