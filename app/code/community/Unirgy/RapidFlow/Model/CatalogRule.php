<?php

class Unirgy_RapidFlow_Model_CatalogRule extends Mage_CatalogRule_Model_Rule
{
	protected function _construct()
    {
        parent::_construct();
        $this->_init('urapidflow/catalogRule');
        $this->setIdFieldName('rule_id');
    }
    
    protected $_multiProductIds = array();
    
	public function getMatchingMultiProductIds($pIds)
    {
    	$pIdsHash = $pIds;
    	if (is_array($pIds)) {
    		sort($pIds);
    		$pIdsHash = implode(',', $pIds);
    	}
    	$pIdsHash = md5($pIdsHash);
        if (!isset($this->_multiProductIds[$pIdsHash])) {
            $this->_multiProductIds[$pIdsHash] = array();
            $this->setCollectedAttributes(array());
            $websiteIds = $this->getWebsiteIds();
	        if (!is_array($websiteIds)) {
	        	$websiteIds = explode(',', $websiteIds);
	        }

            if ($websiteIds) {
                $productCollection = Mage::getResourceModel('catalog/product_collection');

                $productCollection->addWebsiteFilter($websiteIds)->addIdFilter($pIds);
                $this->getConditions()->collectValidatedAttributes($productCollection);

                Mage::getSingleton('core/resource_iterator')->walk(
                    $productCollection->getSelect(),
                    array(array($this, 'callbackValidateMultiProduct')),
                    array(
                        'attributes' => $this->getCollectedAttributes(),
                        'product'    => Mage::getModel('catalog/product'),
                    	'pids_hash' => $pIdsHash,
                    )
                );
            }
        }

        return $this->_multiProductIds[$pIdsHash];
    }
    
	public function callbackValidateMultiProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        if ($this->getConditions()->validate($product)) {
            $this->_multiProductIds[$args['pids_hash']][] = $product->getId();
        }
    }
    
	public function applyAllNoIndex()
    {
        $this->_getResource()->applyAllRulesForDateRange();
        $this->_invalidateCache();
    }
    
	public function applyAllByPids($pIds)
    {
        $this->_getResource()->applyAllRulesForDateRange(null,null,$pIds);
    }
}