<?php

class Unirgy_RapidFlow_Model_Mysql4_CatalogRule_Collection extends Mage_CatalogRule_Model_Mysql4_Rule_Collection
{
	protected function _construct()
    {
        $this->_init('urapidflow/catalogRule');
    }
    
	public function addIsActiveFilter($filterNow=false)
    {
    	if ($filterNow) {
	        $this->getSelect()->where('from_date<=?', now(true));
    	}
	    $this->getSelect()->where('to_date>=? or to_date is null', now(true));
        $this->getSelect()->where('is_active=1');
        return $this;
    }
}