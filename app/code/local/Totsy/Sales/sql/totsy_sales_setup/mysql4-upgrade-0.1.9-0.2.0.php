<?php
$installer = $this;
$installer->startSetup();
$installer->run("
UPDATE sales_order_status_label 
SET label = 'Splitted'
WHERE status = 'splitted'
");

$installer->endSetup();
