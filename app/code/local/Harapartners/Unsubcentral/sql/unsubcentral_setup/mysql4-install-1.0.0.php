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
    DROP TABLE IF EXISTS `{$this->getTable('unsubcentral_item')}`;
    CREATE TABLE `{$this->getTable('unsubcentral_item')}` (
      `unsubcentral_request_id` int(7) unsigned NOT NULL auto_increment,
      `subscriber_email` varchar(150) character set latin1 collate latin1_general_ci NOT NULL default '',
      `unsubcentral_api_status` int(3) NOT NULL default '0',  
      `update_at` datetime NOT NULL default '0000-00-00 00:00:00',
      `error_message` text,
      PRIMARY KEY  (`unsubcentral_request_id`),
      UNIQUE KEY (`subscriber_email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Unsubcentral';
");

$installer->endSetup();
