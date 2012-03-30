<?php

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


//  `import_set_id` int(10) unsigned NOT NULL default '0',
//  `import_row_content` mediumtext NOT NULL default '',

//-- DROP TABLE IF EXISTS {$this->getTable('import/importset')};
//CREATE TABLE {$this->getTable('import/importset')} (
//  `import_importset_id` int(11) unsigned NOT NULL auto_increment,
//  `import_import_id` int(11) unsigned NOT NULL Default '0',
//  `import_importset_row_content` mediumtext NOT NULL default '',
//  `created_time` datetime NULL,
//  `update_time` datetime NULL,
//   PRIMARY KEY (`import_importset_id`), 
//   KEY `FK_IMPORT_IMPORT_ID` (`import_import_id`),
//   CONSTRAINT `FK_IMPORT_IMPORT_ID` foreign key (`import_import_id`) REFERENCES {$installer->getTable('import/import')} (`import_import_id`) ON DELETE CASCADE ON UPDATE CASCADE
//)ENGINE=InnoDB DEFAULT CHARSET=utf8;
