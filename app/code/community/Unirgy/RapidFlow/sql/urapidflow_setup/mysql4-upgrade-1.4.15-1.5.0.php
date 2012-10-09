<?php

$this->startSetup();

if (Mage::helper('urapidflow')->hasMageFeature('table.product_relation')) {
    $this->run("
    DELETE FROM {$this->getTable('catalog_product_relation')} WHERE EXISTS (
        SELECT * FROM {$this->getTable('catalog_product_link')} l 
        INNER JOIN {$this->getTable('catalog_product_link_type')} t USING(link_type_id)
        WHERE l.product_id=parent_id AND l.linked_product_id=child_id AND t.code IN ('relation','up_sell','cross_sell')
    );
    ");
}

$this->endSetup();