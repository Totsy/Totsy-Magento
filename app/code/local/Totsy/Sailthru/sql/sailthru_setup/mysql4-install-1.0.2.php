<?php

$installer = $this;
$installer->startSetup();
 
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('sailthru/feedconfig')};
CREATE TABLE {$this->getTable('sailthru/feedconfig')} (
  `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `order` smallint(1) NOT NULL DEFAULT '1',
  `start_at_day` datetime DEFAULT NULL,
  `start_at_time` datetime DEFAULT NULL,
  `exclude` text DEFAULT NULL,
  `filter` text DEFAULT NULL,
  `hash` varchar(40) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`entity_id`),
  KEY `type` (`type`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='Feed Config';

");
 
$installer->endSetup();

/*
order
  	- event
  		- id
  			- desc - 1
  			- asc - 2
  	- product
		- event ( id ) 
  			- desc - 1
  			- asc - 2
		- sales volume
  			- desc - 3
  			- asc - 4
		- event & sales volume
		  	- desc - 5
  			- asc - 6		
*/