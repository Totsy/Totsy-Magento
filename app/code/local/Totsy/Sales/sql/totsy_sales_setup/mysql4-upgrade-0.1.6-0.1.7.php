<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("
UPDATE sales_order_status_state 
SET is_default = '1'
WHERE status = 'payment_failed' AND state = 'payment_failed';
");
 
$installer->endSetup();