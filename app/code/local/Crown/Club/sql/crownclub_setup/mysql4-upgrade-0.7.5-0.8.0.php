<?php
/**
 *
 * @category      Crown
 * @package       Crown_Club
 * @since         0.8.0
 */
$installer = $this;
$installer->startSetup ();

// Add club expiration date attribute
$installer->addAttribute('customer', 'club_created_at', array(
    'type'           => 'datetime',
    'input'          => 'date',
    'validate_rules' => 'a:1:{s:16:"input_validation";s:4:"date";}',
    'visible'        => false,
    'required'       => false
));

$installer->endSetup ();