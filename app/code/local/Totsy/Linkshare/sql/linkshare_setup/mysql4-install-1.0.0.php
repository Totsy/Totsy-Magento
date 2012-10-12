<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('linkshare/transactions')};
CREATE TABLE {$this->getTable('linkshare/transactions')} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `trans_id` varchar(10) default NULL,
  `customertracking_id` int(10) unsigned NOT NULL default 0,
  `order_id` varchar(255) default NULL,
  `raw_data` varchar(255) NOT NULL default '',
  `order_status` varchar(32) NOT NULL default '',
  `trans_status` varchar(32) NOT NULL default '',
  `message` varchar(255) NOT NULL default '',
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `FK_LINKSHARE_TRANSACTION_ORDER` (`order_id`),
  INDEX `FK_LINKSHARES_TRANSACTION_ORDER_STATUS`(`order_status`),
  INDEX `FK_LINKSHARES_TRANSACTION_TRANS_STATUS`(`trans_status`) 
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Linkshare Transaction Records';
");
    
$installer->endSetup();

?>
