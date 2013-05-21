<?php
/**
 * @category    Totsy
 * @package     Totsy
 * @author      Tom Royer <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

$installer = $this;
$installer->startSetup ();

$entityTypeId = $installer->getEntityTypeId('catalog_product');
$oldTable = $this->getAttributeTable($entityTypeId,'tax_class');
$taxClassAttributeId = $this->getAttribute($entityTypeId, 'tax_class', 'attribute_id');
$installer->updateAttribute($entityTypeId, $taxClassAttributeId, 'backend_type','varchar');
$newTable = $this->getAttributeTable($entityTypeId,'tax_class');



$query = "
insert into {$newTable}
(entity_type_id,attribute_id,store_id,entity_id,value)
select entity_type_id,attribute_id,store_id,entity_id,value
from {$oldTable}
where attribute_id=225";
$installer->run ($query);

$installer->endSetup ();