<?php
$installer = $this;
$installer->startSetup();
 
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('categoryevent/sortentry')};
CREATE TABLE {$this->getTable('categoryevent/sortentry')} (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date` datetime default NULL,
  `store_id` smallint(5) unsigned DEFAULT NULL,
  `top_live_queue` text NOT NULL default '',
  `live_queue` text NOT NULL default '',  
  `upcoming_queue` text NOT NULL default '',
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  PRIMARY KEY  (`id`),
  KEY `FK_CATEGORYEVENT_sortentry_STORE` (`store_id`),
  CONSTRAINT `FK_CATEGORYEVENT_sortentry_STORE` FOREIGN KEY (`store_id`) REFERENCES {$installer->getTable('core/store')} (`store_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Categoryevent sortentry';

  ");
 
$installer->endSetup();

?>