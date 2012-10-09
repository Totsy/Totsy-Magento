<?php

$this->startSetup();

$this->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('usimpleup_module')}` (
  `module_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `module_name` VARCHAR(255) NOT NULL DEFAULT '',
  `download_uri` TEXT NOT NULL,
  `last_checked` DATETIME DEFAULT NULL,
  `last_downloaded` DATETIME DEFAULT NULL,
  `last_stability` VARCHAR(30) DEFAULT NULL,
  `last_version` VARCHAR(30) DEFAULT NULL,
  `remote_version` VARCHAR(30) DEFAULT NULL,
  `license_key` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`module_id`),
  UNIQUE KEY `NewIndex1` (`module_name`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `{$this->getTable('usimpleup_module')}` (module_name, download_uri)
VALUES ('Unirgy_SimpleUp', 'http://download.unirgy.com/Unirgy_SimpleUp-latest.zip');

");

$this->endSetup();