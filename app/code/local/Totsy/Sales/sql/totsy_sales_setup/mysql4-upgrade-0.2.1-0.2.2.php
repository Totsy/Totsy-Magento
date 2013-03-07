<?php
$installer = $this;
$installer->startSetup();
$installer->run("
INSERT INTO sales_order_status_state 
VALUES
('partially_shipped','processing','0')
");
$installer->run("
INSERT INTO sales_order_status
VALUES
('partially_shipped','Partially Shipped')
");
$installer->endSetup();
