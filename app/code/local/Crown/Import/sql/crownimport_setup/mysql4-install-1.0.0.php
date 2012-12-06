<?php
/**
 *
 * @category 	Crown
 * @package 	Crown_Import
 * @since 		1.0.0
 */

/* @var $this Crown_Import_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create required tables
 */
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('crownimport/importhistory')};
CREATE TABLE {$this->getTable('crownimport/importhistory')} (
  `import_id` int(10) unsigned NOT NULL auto_increment,
  `import_title` varchar(255) NOT NULL default '',
  `import_filename` varchar(255) NOT NULL default '',
  `urapidflow_profile_id` int(10) unsigned default NULL,
  `vendor_id` int(10) unsigned default NULL,
  `vendor_code` varchar(255) NOT NULL default '',
  `po_id` int(10) unsigned default NULL,
  `category_id` int(10) unsigned default NULL,
  `status` smallint(5) unsigned default 0,
  `step` varchar(25) NOT NULL default '',
  `has_configurable` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `urapidflow_profile_id_product_extra` int(10) unsigned default NULL,
  `created_at` datetime NULL,
  `updated_at` datetime NULL,
  PRIMARY KEY (`import_id`),
  KEY `FK_IMPORTHISTORY_IMPORT_VENDOR` (`vendor_id`),
  KEY `FK_IMPORTHISTORY_IMPORT_PO` (`po_id`),
  KEY `FK_IMPORTHISTORY_IMPORT_CATEGORY` (`category_id`),
  CONSTRAINT `FK_IMPORTHISTORY_IMPORT_VENDOR` FOREIGN KEY (`vendor_id`) REFERENCES {$this->getTable('stockhistory/vendor')} (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_IMPORTHISTORY_IMPORT_PO` FOREIGN KEY (`po_id`) REFERENCES {$this->getTable('stockhistory/purchaseorder')} (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_IMPORTHISTORY_IMPORT_CATEGORY` FOREIGN KEY (`category_id`) REFERENCES {$this->getTable('catalog_category_entity')} (`entity_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_IMPORTHISTORY_IMPORT_URAPIDFLOW_PROFILE` FOREIGN KEY (`urapidflow_profile_id`) REFERENCES {$this->getTable('urapidflow/profile')} (`profile_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `FK_IMPORTHISTORY_IMPORT_URAPIDFLOW_PROFILE_PRODUCT_EXTRA` FOREIGN KEY (`urapidflow_profile_id_product_extra`) REFERENCES {$this->getTable('urapidflow/profile')} (`profile_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Import Module With uRapidflow Import Records';
");

$installer->endSetup();