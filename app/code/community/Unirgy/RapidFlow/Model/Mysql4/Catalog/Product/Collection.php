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

class Unirgy_RapidFlow_Model_Mysql4_Catalog_Product_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{
    public function addAttributeToJoin($attributeCode, $joinType='inner')
    {
        $this->_addAttributeJoin($attributeCode, $joinType);
        return $this;
    }

    public function isEnabledFlat()
    {
        if (!Mage::helper('urapidflow')->hasMageFeature('flat_catalog')) {
            return false;
        }
        return parent::isEnabledFlat();
    }

    /**
     * Implementing public method to get attribute table alias, this has changed for Magento 1.6
     * @param string $attributeCode
     * @return string
     */
    public function getAttributeTableAlias($attributeCode)
    {
        return $this->_getAttributeTableAlias($attributeCode);
    }

    /**
    * @see http://groups.google.com/group/magento-devel/browse_thread/thread/cc34b38e176dc529
    */
    protected function _joinPriceRules()
    {
        if ($this->isEnabledFlat()) {
            $customerGroup = Mage::getSingleton('customer/session')->getCustomerGroupId();
            $priceColumn = 'e.display_price_group_' . $customerGroup;
            $this->getSelect()->from(null, array('_rule_price' => $priceColumn));

            return $this;
        }
        $wId = Mage::app()->getStore($this->getStoreId())->getWebsiteId();
        $gId = Mage::getSingleton('customer/session')->getCustomerGroupId();

        $storeDate = Mage::app()->getLocale()->storeTimeStamp($this->getStoreId());
        $conditions  = "_price_rule.product_id = e.entity_id AND ";
        $conditions .= "_price_rule.rule_date = '".$this->getResource()->formatDate($storeDate, false)."' AND ";
        $conditions .= "_price_rule.website_id = '{$wId}' AND ";
        $conditions .= "_price_rule.customer_group_id = '{$gId}'";

        $this->getSelect()->joinLeft(
            array('_price_rule'=>$this->getTable('catalogrule/rule_product_price')),
            $conditions,
            array('_rule_price'=>'rule_price')
        );
        return $this;
    }
}
