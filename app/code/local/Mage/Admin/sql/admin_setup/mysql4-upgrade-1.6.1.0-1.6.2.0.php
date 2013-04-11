<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Admin
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 * @author      Lawrenberg Hanson <lhanson@totsy.com>
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

die("Made it to the setup file");

// Add reset password link token creation date column
$installer->getConnection()->addColumn($installer->getTable('admin/user'), 'department', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable' => FALSE,
    'default' => "",
    'comment' => 'Associated organization department'
));
$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('admin_department')};
CREATE TABLE {$this->getTable('admin_department')} (
  `id` mediumint(9) unsigned NOT NULL auto_increment,
  `department` varchar(32) character set utf8 NOT NULL default '',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Organization Departments';
");
$installer->endSetup();