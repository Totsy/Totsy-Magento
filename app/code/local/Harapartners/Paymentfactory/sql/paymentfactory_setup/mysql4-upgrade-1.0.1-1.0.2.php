<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("
ALTER TABLE {$this->getTable('paymentfactory/profile')}
ADD saved_by_customer SMALLINT(5) NULL;
");
 
$installer->endSetup();