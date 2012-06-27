<?php
$installer = $this;
$installer->startSetup();
$installer->run("
INSERT INTO sales_order_status_state 
VALUES 
('processing_fulfillment','processing','0'),
('fulfillment_aging','processing','0'),
('fulfillment_failed','processing','0'),
('shipment_aging','processing','0')
");
$installer->endSetup();
