<?php
$installer = $this;

$installer->startSetup ();

// Add club member flag to customer model
$installer->addAttribute('customer', 'is_club_member', array(
    'input'          => 'select',
    'visible'        => false,
    'required'       => false,
	'source'		 => 'eav/entity_attribute_source_boolean',
));
	
$installer->endSetup ();