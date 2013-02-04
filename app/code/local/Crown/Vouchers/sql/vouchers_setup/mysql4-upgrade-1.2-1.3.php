<?php
/* @var $installer Crown_Vouchers_Model_Mysql4_Setup */
$installer = $this;

$installer->startSetup ();

// Do nothing due to a commit error.  All properties will be fixed in 1.4
//$installer->upgradeModule_1_2(); // Execute Module Installation

$installer->endSetup ();