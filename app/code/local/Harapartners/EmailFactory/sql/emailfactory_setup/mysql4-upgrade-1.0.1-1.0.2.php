<?php

$installer = $this;
$installer->startSetup();

$installer->run(" ALTER TABLE  `params` CHANGE  `params`  `params` LONGTEXT ");

$installer->endSetup();