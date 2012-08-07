<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("
ALTER TABLE {$this->getTable('promotionfactory/virtualproductcoupon')}
ADD order_increment_id int(10) unsigned default NULL;
");

$installer->endSetup();