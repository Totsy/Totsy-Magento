<?php

class Unirgy_RapidFlow_Model_Mysql4_CatalogRule extends Mage_CatalogRule_Model_Mysql4_Rule
{
	/**
     * Remove catalog rules product prices for specified date range and product
     *
     * @param   int|string $fromDate
     * @param   int|string $toDate
     * @param   int|null $productId
     * @return  Mage_CatalogRule_Model_Mysql4_Rule
     */
    public function removeCatalogPricesForDateRange($fromDate, $toDate, $productId=null)
    {
        $write = $this->_getWriteAdapter();
        $conds = array();
        $cond = $write->quoteInto('rule_date between ?', $this->formatDate($fromDate));
        $cond = $write->quoteInto($cond.' and ?', $this->formatDate($toDate));
        $conds[] = $cond;
        if (!is_null($productId)) {
            $conds[] = $write->quoteInto('product_id in (?)', $productId);
        }

        /**
         * Add information about affected products
         * It can be used in processes which related with product price (like catalog index)
         */
        $select = $this->_getWriteAdapter()->select()
            ->from($this->getTable('catalogrule/rule_product_price'), 'product_id')
            ->where(implode(' AND ', $conds));
        $insertQuery = 'REPLACE INTO ' . $this->getTable('catalogrule/affected_product') . ' (product_id)' . $select->__toString();
        $this->_getWriteAdapter()->query($insertQuery);
        $write->delete($this->getTable('catalogrule/rule_product_price'), $conds);
        return $this;
    }

    /**
     * Delete old price rules data
     *
     * @param   int $maxDate
     * @param   mixed $productId
     * @return  Mage_CatalogRule_Model_Mysql4_Rule
     */
    public function deleteOldData($date, $productId=null)
    {
        $write = $this->_getWriteAdapter();
        $conds = array();
        $conds[] = $write->quoteInto('rule_date<?', $this->formatDate($date));
        if (!is_null($productId)) {
            $conds[] = $write->quoteInto('product_id in (?)', $productId);
        }
        $write->delete($this->getTable('catalogrule/rule_product_price'), $conds);
        return $this;
    }
    
	/**
     * Get DB resource statment for processing query result
     *
     * @param   int $fromDate
     * @param   int $toDate
     * @param   int|null $productId
     * @param   int|null $websiteId
     * @return  Zend_Db_Statement_Interface
     */
    protected function _getRuleProductsStmt($fromDate, $toDate, $productId=null, $websiteId = null)
    {
        $read = $this->_getReadAdapter();
        /**
         * Sort order is important
         * It used for check stop price rule condition.
         * website_id   customer_group_id   product_id  sort_order
         *  1           1                   1           0
         *  1           1                   1           1
         *  1           1                   1           2
         * if row with sort order 1 will have stop flag we should exclude
         * all next rows for same product id from price calculation
         */
        $select = $read->select()
            ->from(array('rp'=>$this->getTable('catalogrule/rule_product')))
            ->where($read->quoteInto('rp.from_time=0 or rp.from_time<=?', $toDate)
            ." or ".$read->quoteInto('rp.to_time=0 or rp.to_time>=?', $fromDate))
            ->order(array('rp.website_id', 'rp.customer_group_id', 'rp.product_id', 'rp.sort_order', 'rp.rule_id'));

        if (!is_null($productId)) {
            $select->where('rp.product_id in (?)', $productId);
        }

        /**
         * Join default price and websites prices to result
         */
        $priceAttr  = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'price');
        $priceTable = $priceAttr->getBackend()->getTable();
        $attributeId= $priceAttr->getId();

        $joinCondition = '%1$s.entity_id=rp.product_id AND (%1$s.attribute_id='.$attributeId.') and %1$s.store_id=%2$s';

        $select->join(
            array('pp_default'=>$priceTable),
            sprintf($joinCondition, 'pp_default', Mage_Core_Model_App::ADMIN_STORE_ID),
            array('default_price'=>'pp_default.value')
        );

        if ($websiteId !== null) {
            $website  = Mage::app()->getWebsite($websiteId);
            $defaultGroup = $website->getDefaultGroup();
            if ($defaultGroup instanceof Mage_Core_Model_Store_Group) {
                $storeId    = $defaultGroup->getDefaultStoreId();
            } else {
                $storeId    = Mage_Core_Model_App::ADMIN_STORE_ID;
            }

            $select->joinInner(
                array('product_website'=>$this->getTable('catalog/product_website')),
                'product_website.product_id=rp.product_id AND rp.website_id=product_website.website_id AND product_website.website_id='.$websiteId,
                array()
            );

            $tableAlias = 'pp'.$websiteId;
            $fieldAlias = 'website_'.$websiteId.'_price';
            $select->joinLeft(
                array($tableAlias=>$priceTable),
                sprintf($joinCondition, $tableAlias, $storeId),
                array($fieldAlias=>$tableAlias.'.value')
            );
        } else {
            foreach (Mage::app()->getWebsites() as $website) {
                $websiteId  = $website->getId();
                $defaultGroup = $website->getDefaultGroup();
                if ($defaultGroup instanceof Mage_Core_Model_Store_Group) {
                    $storeId    = $defaultGroup->getDefaultStoreId();
                } else {
                    $storeId    = Mage_Core_Model_App::ADMIN_STORE_ID;
                }

                $storeId    = $defaultGroup->getDefaultStoreId();
                $tableAlias = 'pp'.$websiteId;
                $fieldAlias = 'website_'.$websiteId.'_price';
                $select->joinLeft(
                    array($tableAlias=>$priceTable),
                    sprintf($joinCondition, $tableAlias, $storeId),
                    array($fieldAlias=>$tableAlias.'.value')
                );
            }
        }
        return $read->query($select);
    }
    
	public function updateRuleMultiProductData(Mage_CatalogRule_Model_Rule $rule, $pIds)
    {
        $ruleId = $rule->getId();
        $write = $this->_getWriteAdapter();
        $write->beginTransaction();

        $write->delete($this->getTable('catalogrule/rule_product'), $write->quoteInto('rule_id=?', $ruleId));

        if (!$rule->getIsActive()) {
            $write->commit();
            return $this;
        }

        $websiteIds = $rule->getWebsiteIds();
        if (empty($websiteIds)) {
        	$write->commit();
            return $this;
        }
        if (!is_array($websiteIds)) {
        	$websiteIds = explode(',', $websiteIds);
        }
        Varien_Profiler::start('__MATCH_PRODUCTS__');
        $productIds = $rule->getMatchingMultiProductIds($pIds);
        Varien_Profiler::stop('__MATCH_PRODUCTS__');
        $customerGroupIds = $rule->getCustomerGroupIds();

        $fromTime = strtotime($rule->getFromDate());
        $toTime = strtotime($rule->getToDate());
        $toTime = $toTime ? ($toTime + self::SECONDS_IN_DAY - 1) : 0;

        $sortOrder = (int)$rule->getSortOrder();
        $actionOperator = $rule->getSimpleAction();
        $actionAmount = $rule->getDiscountAmount();
        $actionStop = $rule->getStopRulesProcessing();

        $rows = array();
        $queryStart = 'INSERT INTO '.$this->getTable('catalogrule/rule_product').' (
                rule_id, from_time, to_time, website_id, customer_group_id, product_id, action_operator,
                action_amount, action_stop, sort_order ) values ';
        $queryEnd = ' ON DUPLICATE KEY UPDATE action_operator=VALUES(action_operator),
            action_amount=VALUES(action_amount), action_stop=VALUES(action_stop)';
        try {
            foreach ($productIds as $productId) {
                foreach ($websiteIds as $websiteId) {
                    foreach ($customerGroupIds as $customerGroupId) {
                        $rows[] = "('" . implode("','", array(
                            $ruleId,
                            $fromTime,
                            $toTime,
                            $websiteId,
                            $customerGroupId,
                            $productId,
                            $actionOperator,
                            $actionAmount,
                            $actionStop,
                            $sortOrder))."')";
                        /**
                         * Array with 1000 rows contain about 2M data
                         */
                        if (sizeof($rows)==1000) {
                            $sql = $queryStart.join(',', $rows).$queryEnd;
                            $write->query($sql);
                            $rows = array();
                        }
                    }
                }
            }
            if (!empty($rows)) {
                $sql = $queryStart.join(',', $rows).$queryEnd;
                $write->query($sql);
            }

            $write->commit();
        } catch (Exception $e) {
            $write->rollback();
            throw $e;
        }

        return $this;
    }
}