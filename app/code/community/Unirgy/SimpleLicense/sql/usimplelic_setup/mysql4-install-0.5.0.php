<?php

$this->startSetup();

$this->run("

CREATE TABLE IF NOT EXISTS `{$this->getTable('usimplelic_license')}` (
  `license_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `license_key` VARCHAR(255) NOT NULL DEFAULT '',
  `license_status` VARCHAR(50) NOT NULL,
  `last_checked` DATETIME DEFAULT NULL,
  `last_status` VARCHAR(20) DEFAULT NULL,
  `last_error` TEXT,
  `retry_num` TINYINT(4) DEFAULT NULL,
  `products` TEXT NOT NULL,
  `server_restriction` TEXT NOT NULL,
  `license_expire` DATETIME DEFAULT NULL,
  `upgrade_expire` DATETIME DEFAULT NULL,
  `signature` TEXT,
  PRIMARY KEY (`license_id`),
  UNIQUE KEY `IDX_license_key` (`license_key`)
) ENGINE=MYISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1

");

try {
    $this->run("INSERT INTO `{$this->getTable('usimpleup_module')}` (module_name, download_uri)
        VALUES ('Unirgy_SimpleLicense', 'http://download.unirgy.com/Unirgy_SimpleLicense-latest.zip')");
} catch (Exception $e) {
    // already exists
}

$this->endSetup();