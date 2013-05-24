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
 * @category    Enterprise
 * @package     Enterprise_CustomerSegment
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Skipped upgrade
 */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->insert($installer->getTable('eav_attribute'), array(
    'entity_type_id' => '1',
    'attribute_code' => 'created_days',
    'backend_type' => 'static',
    'frontend_input' => 'days',
    'frontend_label' => 'Created Days',
    'is_required' => 0,
    'is_user_defined' => 0,
    'is_unique' => 0
));

$attribute_id = $installer->getConnection()->lastInsertId();

$installer->getConnection()->insert($installer->getTable('customer/eav_attribute'), array(
    'attribute_id' => $attribute_id,
    'is_visible' => 0,
    'multiline_count' => 0,
    'is_system' => 0,
    'sort_order' => 0,
    'is_used_for_customer_segment' => 1
));

$installer->endSetup();