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
DROP TABLE IF EXISTS {$this->getTable('totsyapi/client')};
CREATE TABLE {$this->getTable('totsyapi/client')}(
	`totsyapi_client_id` int(10) unsigned NOT NULL auto_increment,
	`name`	        varchar(50) NOT NULL default '',
	`contact_info`	varchar(50) NOT NULL default '',
	`authorization`	varchar(50) NOT NULL default '',
	`active`        tinyint(1) NOT NULL default 1,
	`last_request`  datetime default NULL,
	`created_at`	datetime default NULL,
	`updated_at` 	datetime default NULL,
	PRIMARY KEY	(`totsyapi_client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Totsy API Client';
");

$installer->endSetup();

?>