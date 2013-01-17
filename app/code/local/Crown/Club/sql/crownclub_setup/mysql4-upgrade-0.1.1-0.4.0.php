<?php
/**
 *
 * @category 	Crown
 * @package 	Crown_Club
 * @since 		0.4.0
 */
$installer = $this;
$installer->startSetup ();

$query = "
ALTER TABLE `{$this->getTable('sales/order')}`
ADD `customer_is_club_member` tinyint(1) DEFAULT 0 COMMENT 'Customer is club member at time of purchase';
";
//$installer->run ($query);

$installer->endSetup ();