<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `{$installer->getTable('wsalogger_log')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('wsalogger_log')}` (
  `notification_id` int(10) unsigned NOT NULL auto_increment,
  `severity` tinyint(3) unsigned NOT NULL default '0',
  `date_added` datetime NOT NULL,
  `extension` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `code` varchar(255),
  `url` varchar(255),
  `is_read` tinyint(1) unsigned NOT NULL default '0',
  `is_remove` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY (`notification_id`),
  KEY `IDX_SEVERITY` (`severity`),
  KEY `IDX_IS_READ` (`is_read`),
  KEY `IDX_IS_REMOVE` (`is_remove`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();