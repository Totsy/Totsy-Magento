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
  `import_import_id` int(10) unsigned NOT NULL auto_increment,
  `import_title` varchar(255) NOT NULL default '',
  `import_filename` varchar(255) NOT NULL default '',
  `import_batch_id` int(10) unsigned NOT NULL,
  `vendor_id` int(10) unsigned default NULL,
  `vendor_code` varchar(255) NOT NULL default '',
  `po_id` int(10) unsigned default NULL,
  `category_id` int(10) unsigned default NULL,
  `action_type` smallint(5) unsigned default NULL,
  `error_message` text default NULL,
  `status` smallint(5) unsigned default NULL,
  `created_at` datetime NULL,
  `updated_at` datetime NULL,
  
  PRIMARY KEY (`import_import_id`),
  KEY `FK_IMPORT_IMPORT_VENDOR` (`vendor_id`),
  KEY `FK_IMPORT_IMPORT_PO` (`po_id`),
  KEY `FK_IMPORT_IMPORT_CATEGORY` (`category_id`),
  CONSTRAINT `FK_IMPORT_IMPORT_VENDOR` FOREIGN KEY (`vendor_id`) REFERENCES {$this->getTable('stockhistory/vendor')} (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_IMPORT_IMPORT_PO` FOREIGN KEY (`po_id`) REFERENCES {$this->getTable('stockhistory/purchaseorder')} (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_IMPORT_IMPORT_CATEGORY` FOREIGN KEY (`category_id`) REFERENCES {$this->getTable('catalog_category_entity')} (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE
  
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Import Module Import Records';

    ");


$installer->endSetup();
