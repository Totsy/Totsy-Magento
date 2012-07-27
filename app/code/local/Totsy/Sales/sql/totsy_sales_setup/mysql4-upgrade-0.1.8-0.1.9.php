<?php
$installer = $this;
$installer->startSetup();
$installer->run("
INSERT INTO sales_order_status_state 
VALUES
('batch_cancel_csr_review','processing','0')
");
$installer->run("
INSERT INTO sales_order_status
VALUES
('batch_cancel_csr_review','Batch Cancel - CSR Review')
");
$installer->endSetup();
