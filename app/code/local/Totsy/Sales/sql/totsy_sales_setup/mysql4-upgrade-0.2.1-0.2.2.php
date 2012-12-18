<?php
$installer = $this;
$installer->startSetup();

$installer->run("
  UPDATE sales_order_status
  SET label = 'Customer Service review needed / Batch canceled'
  WHERE label = 'Batch Cancel - CSR Review';
");



$installer->endSetup();