<?php
$installer = $this;
$installer->startSetup();
$installer->run("
INSERT INTO sales_order_status_state
VALUES
('pending','processing','0'),
('complete','processing','0'),
('fraud','processing','0'),
('pending','payment_failed','0')
");
$installer->endSetup();