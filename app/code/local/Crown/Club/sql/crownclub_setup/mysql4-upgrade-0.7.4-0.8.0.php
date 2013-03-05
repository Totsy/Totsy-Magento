<?php
/**
 *
 * @category      Crown
 * @package       Crown_Club
 * @since         0.8.0
 */
$installer = $this;
$installer->startSetup();

// Add reset password link token attribute
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

$installer->endSetup();