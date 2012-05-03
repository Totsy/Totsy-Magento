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
    DROP TABLE IF EXISTS `{$this->getTable('childrenlist_child')}`;
    CREATE TABLE `{$this->getTable('childrenlist_child')}` (
      `child_id` int(10) unsigned NOT NULL auto_increment,
      `customer_id` int(10) unsigned NOT NULL default '0',
      `child_name` varchar(255) character set latin1 collate latin1_general_ci NOT NULL default '',
      `child_gender` tinyint(3) NOT NULL default '0',
      `child_customer_relationship` tinyint(3) NOT NULL default '0',
      `child_birthday` date default NULL,
      `additional_data` text character set latin1 collate latin1_general_ci NOT NULL default '',
      `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
        `updated_at` datetime default NULL,
      PRIMARY KEY  (`child_id`),
      KEY `FK_CHILDRENTLIST_CUSTOMER` (`customer_id`),
      CONSTRAINT `FK_CHILDRENTLIST_CUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Children List';
");

$installer->endSetup();
