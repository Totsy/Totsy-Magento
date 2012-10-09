<?php

$this->startSetup();

try { $this->run("set session old_alter_table=1"); } catch (Exception $e) {}

$this->run("
ALTER IGNORE TABLE {$this->getTable('catalog_product_entity_media_gallery')}
ADD UNIQUE KEY UNQ_GALLERY_ENTRY (entity_id,attribute_id,value);
DROP INDEX UNQ_GALLERY_ENTRY ON {$this->getTable('catalog_product_entity_media_gallery')};
");

$this->endSetup();