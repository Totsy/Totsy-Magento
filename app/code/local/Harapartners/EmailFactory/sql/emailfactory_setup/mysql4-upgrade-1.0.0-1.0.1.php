<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    DROP TABLE IF EXISTS `{$this->getTable('sailthru_queue')}`;
    CREATE TABLE `{$this->getTable('sailthru_queue')}` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `call` text,
	  `params` text,
	  `created_at` datetime DEFAULT NULL,
	  `stats` text,
	  `status` varchar(11) DEFAULT NULL,
	  `additional_calls` text,
	  `updated_at` datetime DEFAULT NULL,
	  PRIMARY KEY (`id`),
	  KEY `created_at` (`created_at`),
	  KEY `status` (`status`),
	  KEY `updated_at` (`updated_at`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
");

$installer->endSetup();
