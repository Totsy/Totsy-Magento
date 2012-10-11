<?php

$this->startSetup();

try {

$this->run("

ALTER TABLE `{$this->getTable('usimplelic_license')}`
    ADD COLUMN `server_info` TEXT NULL;

");

} catch (Exception $e) {
    // already exists
}

$this->endSetup();