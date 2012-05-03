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
UPDATE `customertracking_record` SET `login_count` = 0;
ALTER TABLE `customertracking_record` CHANGE `login_count` `level` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
UPDATE `eav_attribute` SET `backend_type` = 'varchar' WHERE `attribute_code` = 'login_counter' AND `entity_type_id` = 1 LIMIT 1 ;
");
    
$installer->endSetup();

?>