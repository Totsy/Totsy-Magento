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

DROP TABLE IF EXISTS {$this->getTable('import/import')};
CREATE TABLE {$this->getTable('import/import')} (
  `import_import_id` int(11) unsigned NOT NULL auto_increment,
  `import_title` varchar(255) NOT NULL default '',
  `import_filename` varchar(255) NOT NULL default '',
  `import_status` varchar(255) NOT NULL,
  `import_batch_id` int(10) unsigned NOT NULL,
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`import_import_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup();
