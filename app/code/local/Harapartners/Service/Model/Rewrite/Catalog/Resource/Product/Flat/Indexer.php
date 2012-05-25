<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Service_Model_Rewrite_Catalog_Resource_Product_Flat_Indexer extends Mage_Catalog_Model_Resource_Product_Flat_Indexer {

    public function updateStaticAttributes($storeId, $productIds = null) {
        if (!$this->_isFlatTableExists($storeId)) {
            return $this;
        }
        $adapter   = $this->_getWriteAdapter();
        $websiteId = (int)Mage::app()->getStore($storeId)->getWebsite()->getId();
        /* @var $status Mage_Eav_Model_Entity_Attribute */
        $status    = $this->getAttribute('status');

        $fieldList  = array('entity_id', 'type_id', 'attribute_set_id');
        $colsList   = array('entity_id', 'type_id', 'attribute_set_id');
        if ($this->getFlatHelper()->isAddChildData()) {
            $fieldList = array_merge($fieldList, array('child_id', 'is_child'));
            $isChild   = new Zend_Db_Expr('0');
            $colsList  = array_merge($colsList, array('entity_id', $isChild));
        }

        $columns    = $this->getFlatColumns();
        $bind       = array(
            'website_id'     => $websiteId,
            'store_id'       => $storeId,
            'entity_type_id' => (int)$status->getEntityTypeId(),
            'attribute_id'   => (int)$status->getId()
        );

        $fieldExpr = $adapter->getCheckSql('t2.value_id > 0', 't2.value', 't1.value');
        $select     = $this->_getWriteAdapter()->select()
            ->from(array('e' => $this->getTable('catalog/product')), $colsList)
            ->join(
                array('wp' => $this->getTable('catalog/product_website')),
                'e.entity_id = wp.product_id AND wp.website_id = :website_id',
                array())
            ->joinLeft(
                array('t1' => $status->getBackend()->getTable()),
                'e.entity_id = t1.entity_id',
                array())
            ->joinLeft(
                array('t2' => $status->getBackend()->getTable()),
                't2.entity_id = t1.entity_id'
                    . ' AND t1.entity_type_id = t2.entity_type_id'
                    . ' AND t1.attribute_id = t2.attribute_id'
                    . ' AND t2.store_id = :store_id',
                array())
            ->where('t1.entity_type_id = :entity_type_id')
            ->where('t1.attribute_id = :attribute_id')
            ->where('t1.store_id = ?', Mage_Core_Model_App::ADMIN_STORE_ID)
            ->where("{$fieldExpr} = ?", Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        foreach ($this->getAttributes() as $attributeCode => $attribute) {
            /** @var $attribute Mage_Eav_Model_Entity_Attribute */
            if ($attribute->getBackend()->getType() == 'static') {
                if (!isset($columns[$attributeCode])) {
                    continue;
                }
                $fieldList[] = $attributeCode;
                $select->columns($attributeCode, 'e');
            }
        }

        if ($productIds !== null) {
            $select->where('e.entity_id IN(?)', $productIds);
        }
        
        //Harapartners, Jun, START: Fix for INSERT...SELECT problem for multi-DB sync
        $select->bind($bind); //Force bind
        $sql = $select->insertFromSelect($this->getFlatTableName($storeId), $fieldList);
        try{
        	$adapter->query($sql);
        }catch(Exception $e){
        	$adapter->query($sql, $bind);
        }
        //Harapartners, Jun, END

        return $this;
    }

}