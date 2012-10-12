<?php

class Unirgy_RapidFlow_Model_Mysql4_ProductIndexerPrice extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price
{
	protected $_wdtPrepared;
	public function prepareWebsiteDateTable()
    {
    	if (!$this->_wdtPrepared) {
    		$this->_prepareWebsiteDateTable();
    		$this->_wdtPrepared = true;
    	}
    	return $this;
    }
}