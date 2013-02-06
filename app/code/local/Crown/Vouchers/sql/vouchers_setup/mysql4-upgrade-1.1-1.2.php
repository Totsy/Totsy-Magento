<?php
/* @var $installer Crown_Vouchers_Model_Mysql4_Setup */
$installer = $this;

$installer->startSetup ();

$installer->upgradeModule_1_2(); // Execute Module Installation

$installer->endSetup ();