<?php

$installer = $this;
$installer->startSetup();
 
$installer->run("
ALTER TABLE  {$this->getTable('sailthru/feedconfig')} 
CHANGE  `start_at_day`  `start_at_day` DATE NULL DEFAULT NULL
");
 
$installer->endSetup();

?>