<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_CatalogTest
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */
class Unirgy_RapidFlow_Model_Rule extends Mage_Rule_Model_Rule
{
    protected $_productIds = null;

    public function parseConditionsPost($profile, array $rulePost)
    {
        $arr = $this->_convertFlatToRecursive($rulePost);
        if (isset($arr['conditions'])) {
            $profile->setConditions(
                $this->getConditions()
                    ->setConditions(array())
                    ->loadArray($arr['conditions'][1])
                    ->asArray()
            );
        }
        return $this;
    }

    public function getProductIds($profile)
    {
        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $collection = Mage::getResourceModel('urapidflow/catalog_product_collection')
            ->setStore($profile->getStoreId());

        // collect conditions and join validated attributes
        $where = $this->getConditions()->asSqlWhere($collection);
        $_wf   = $profile->getData('options/export/websites_filter');
        $_scs  = $profile->getData('options/export/skip_configurable_simples');
        if (!$where && !$_wf && !$_scs) {
            return true;
        }
        if ($where) $collection->getSelect()->where($where);
        if ($_wf && !$collection->hasFlag('websites_filtered')) {
            $collection->getSelect()->join(
                array('__pw'=>$collection->getTable('catalog/product_website')),
                'e.entity_id=__pw.product_id',
                array()
            );
            $collection->getSelect()->where('__pw.website_id in (?)', $_wf);
            $collection->setFlag('websites_filtered', true);
        }
        if ($_scs && !$collection->hasFlag('skip_configurable_simples')) {
            $collection->getSelect()->joinLeft(
                array('__psl'=>$collection->getTable('catalog/product_super_link')),
                'e.entity_id=__psl.product_id',
                array()
            );
            $collection->getSelect()->where('__psl.product_id is NULL');
            $collection->setFlag('skip_configurable_simples', true);
        }
        return $collection->getAllIds();
    }

    public function getConditionsInstance()
    {
        return Mage::getModel('urapidflow/rule_condition_combine');
    }
}