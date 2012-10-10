<?php

$this->startSetup();

try {
    $this->run("ALTER TABLE `{$this->getTable('usimpleup_module')}` ADD COLUMN `license_key` VARCHAR(255) DEFAULT NULL");
} catch (Exception $e) {
    // column already exists
}
$this->endSetup();