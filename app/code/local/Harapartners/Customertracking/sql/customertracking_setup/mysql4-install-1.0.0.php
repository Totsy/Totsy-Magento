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

-- DROP TABLE IF EXISTS {$this->getTable('customertracking/record')};
CREATE TABLE {$this->getTable('customertracking/record')} (
  `customertracking_id` int(10) unsigned NOT NULL auto_increment,
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  `affiliate_id` int(10) unsigned NOT NULL default '0',
  `customer_id` int(10) unsigned NOT NULL default '0',  
  `customer_email` varchar(255) NOT NULL default '',
  `affiliate_code` text NOT NULL default '',
  `sub_affiliate_code` text NOT NULL default '',
  `registration_param` text NOT NULL default '',
  `login_count` int(10) unsigned NOT NULL default '0',
  `page_view_count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`customertracking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Customer Tracking Record';

  ");
 
$installer->endSetup();

?>