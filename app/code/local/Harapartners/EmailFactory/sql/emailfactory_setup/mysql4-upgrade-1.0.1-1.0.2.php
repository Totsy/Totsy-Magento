<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `{$this->getTable('emailfactory/sailthruqueue')}` MODIFY `params` LONGTEXT");

$installer->endSetup();
