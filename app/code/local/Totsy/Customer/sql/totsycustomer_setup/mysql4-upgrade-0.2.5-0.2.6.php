<?php
/**
 * @category    Totsy
 * @package     Totsy
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

$installer = $this;
$installer->startSetup();

$installer->addAttribute('customer', 'is_internal_user', array(
    'label'        => 'Is Internal User',
    'visible'      => true,
    'required'     => false,
    'type' => 'int',
    'input' => 'select',
    'user_defined' => '1',
    'group' => 'General Information',
    'source' => 'eav/entity_attribute_source_boolean',
));

$attributeId = (int)$installer->getAttribute('customer', 'is_internal_user', 'attribute_id');

$table = $installer->getTable('customer/form_attribute');

$installer->run("INSERT INTO {$table} (`form_code`,`attribute_id`) values ('adminhtml_customer','{$attributeId}')");

$installer->endSetup();