<?php
/**
 *
 * @category      Crown
 * @package       Crown_Club
 * @since         0.7.0
 */
$installer = $this;
$installer->startSetup ();

$categoryEntityId = $installer->getEntityTypeId('catalog_category');
$installer->addAttribute($categoryEntityId, 'club_only_event', array(
     'type' => 'int',
     'input' => 'select',
     'label' => 'Club Only Event',
     'required' => '0',
     'user_defined' => '1',
     'group' => 'General Information',
     'source' => 'eav/entity_attribute_source_boolean',
));

$installer->endSetup ();