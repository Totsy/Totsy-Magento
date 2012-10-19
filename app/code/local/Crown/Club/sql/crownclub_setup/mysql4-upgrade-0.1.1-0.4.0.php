<?php
/**
 *
 * @category 	Crown
 * @package 	Crown_Club
 * @since 		1.2.0
 */
$installer = $this;
$installer->startSetup ();

$installer->addAttribute('order', 'customer_is_club', array('type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN));

$installer->endSetup ();