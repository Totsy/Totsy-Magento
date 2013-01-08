<?php
/**
 *
 * @category 	Crown
 * @package 	Crown_Import
 * @since 		1.3.0
 */

/* @var $this Crown_Import_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create required tables
 */
$installer->run("
ALTER TABLE {$this->getTable('urapidflow/profile')}
  ADD `error_messages` TEXT
");

$installer->endSetup();