<?php

$this->startSetup();

if (Mage::helper('catalog')->isPriceGlobal()) {
    $delAttrIdsSel = $this->_conn->select()
        ->from(array('a' => $this->getTable('eav/attribute')), array('attribute_id'))
        ->join(array('e' => $this->getTable('eav/entity_type')), 'e.entity_type_id=a.entity_type_id', array())
        ->where("e.entity_type_code='catalog_product'")
        ->where("a.backend_model='catalog/product_attribute_backend_price'");

    $delAttrValuesSql = sprintf('delete from %s where store_id!=0 and attribute_id in (%s)',
        $this->getTable('catalog/product').'_decimal',
        $delAttrIdsSel
    );
    $this->run($delAttrValuesSql);
}

$this->endSetup();