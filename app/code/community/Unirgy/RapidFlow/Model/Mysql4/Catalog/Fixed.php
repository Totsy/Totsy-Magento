<?php

class Unirgy_RapidFlow_Model_Mysql4_Catalog_Fixed
    extends Unirgy_RapidFlow_Model_Mysql4_Abstract_Fixed
{
    protected function _importFetchNewDataIds()
    {
        $fieldValues = array();
        foreach ($this->_newRows as $lineNum=>$row) {
            $cmd = $row[0][0];
            $rowType = $cmd==='+' || $cmd==='-' || $cmd==='%' ? substr($row[0], 1) : $row[0];
            if (empty($this->_rowTypeFields[$rowType]['columns'])) {
                continue;
            }
            foreach ($this->_rowTypeFields[$rowType]['columns'] as $fieldName=>$fieldNode) {
                $col = (int)$fieldNode->col;
                if (!empty($row[$col])) {
                    $fieldValues[$fieldName][$lineNum] = $row[$col];
                }
            }
        }
        $skus = !empty($fieldValues['sku']) ? $fieldValues['sku'] : array();
        if (!empty($fieldValues['linked_sku'])) {
            $skus = array_merge($skus, $fieldValues['linked_sku']);
        }
        if (!empty($fieldValues['selection_sku'])) {
            $skus = array_merge($skus, $fieldValues['selection_sku']);
        }
        if (!empty($skus)) {
            if (sizeof($this->_skus)>$this->_maxCacheItems['sku']) {
                $this->_skus = array();
            }
            $skus1 = array();
            foreach ($skus as $sku) {
                $skus1[] = is_numeric($sku) ? "'".$sku."'" : $this->_write->quote($sku);
            }
            $rows = $this->_read->fetchAll("select entity_id, sku from {$this->_t('catalog/product')} where sku in (".join(',', $skus1).")");
            foreach ($rows as $r) {
                $this->_skus[$r['sku']] = $r['entity_id'];
            }
        }
    }

    protected function _getIdBySku($sku)
    {
        if (empty($this->_skus[$sku])) {
            Mage::throwException($this->__('Invalid SKU (%s)', $sku));
        }
        return $this->_skus[$sku];
    }

    protected function _importProcessNewData()
    {
        parent::_importProcessNewData();

        $this->_importFetchNewDataIds();
    }
}