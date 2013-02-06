<?php
$installer = $this;

$installer->startSetup ();

// Create attribute for is_club_subscription
$installer->addAttribute('catalog_product', 'is_club_subscription', array(
        'group'         => 'Recurring Profile',
        'backend'       => 'catalog/product_attribute_backend_boolean',
        'frontend'      => '',
        'label'         => 'Is Club Membership',
        'input'         => 'select',
        'class'         => '',
        'source'        => 'eav/entity_attribute_source_boolean',
        'global'        => true,
        'visible'       => true,
        'required'      => false,
        'user_defined'  => false,
        'default'       => '',
        'apply_to'      => 'virtual',
        'is_configurable'  => 0,
        'visible_on_front' => false
));

// Add club expiration date attribute
$installer->addAttribute('customer', 'club_expiration_date', array(
    'type'           => 'datetime',
    'input'          => 'date',
    'validate_rules' => 'a:1:{s:16:"input_validation";s:4:"date";}',
    'visible'        => false,
    'required'       => false
));
	
$installer->endSetup ();