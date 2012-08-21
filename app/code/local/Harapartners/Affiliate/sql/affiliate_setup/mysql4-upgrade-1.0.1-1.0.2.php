<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("
ALTER TABLE {$this->getTable('affiliate/record')}
  DROP `sub_affiliate_code`
;
");
 
$installer->endSetup();