<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

$this->startSetup();

$this->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('urapidflow_profile')}` (
  `profile_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `profile_type` varchar(20) NOT NULL DEFAULT 'import',
  `profile_status` varchar(20) NOT NULL DEFAULT 'disabled',
  `media_type` varchar(20) NOT NULL DEFAULT 'csv',
  `run_status` varchar(20) NOT NULL DEFAULT 'idle',
  `invoke_status` varchar(20) NOT NULL DEFAULT 'none',
  `data_type` varchar(255) NOT NULL,
  `base_dir` text not null,
  `filename` varchar(255) NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `rows_found` int(10) unsigned NOT NULL,
  `rows_processed` int(10) unsigned NOT NULL,
  `rows_success` int(10) unsigned NOT NULL,
  `rows_nochange` int(10) unsigned NOT NULL,
  `rows_empty` int(10) unsigned NOT NULL,
  `rows_depends` int(10) unsigned NOT NULL,
  `rows_errors` int(10) unsigned NOT NULL,
  `num_errors` int(10) unsigned NOT NULL,
  `num_warnings` int(10) unsigned NOT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `snapshot_at` datetime DEFAULT NULL,
  `paused_at` datetime DEFAULT NULL,
  `stopped_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `time_elapsed` int(10) unsigned NOT NULL,
  `last_user_id` mediumint(9) unsigned DEFAULT NULL,
  `columns_json` text,
  `options_json` text,
  `conditions_json` text,
  `current_activity` varchar(100),
  `profile_state_json` text,
  `memory_usage` int(10) unsigned DEFAULT NULL,
  `memory_peak_usage` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

/*
  `profile_type` enum('import','export') NOT NULL DEFAULT 'import',
  `profile_status` enum('enabled','disabled') NOT NULL DEFAULT 'disabled',
  `media_type` enum('csv') NOT NULL DEFAULT 'csv',
  `run_status` enum('idle','pending','running','paused','stopped','finished') NOT NULL DEFAULT 'pending',
  `invoke_status` enum('none','foreground','ondemand','scheduled') NOT NULL DEFAULT 'none',
*/

// fixing issues with Mage_Eav_Model_Config::_createAttribute():641 until it's fixed in Magento core
$this->run("
update {$this->getTable('eav_attribute')} set attribute_model=null where attribute_model='';
");

$this->endSetup();