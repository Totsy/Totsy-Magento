<?php
/**
 * {magecore_license_notice}
 *
 * @category   Oro
 * @package    Oro_Configurable
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */

/**
 * Optimized Catalog Configurable Product Attribute Collection
 */
class Oro_Configurable_Model_Resource_Product_Type_Configurable_Attribute_Collection
    extends Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
{
    /**
     * Add product attributes to collection items
     *
     * @return Oro_Configurable_Model_Resource_Product_Type_Configurable_Attribute_Collection
     */
    protected function _addProductAttributes()
    {
        foreach ($this->_items as $item) {
            $productAttribute = $this->getProduct()->getResource()->getAttribute($item->getAttributeId());
            $item->setProductAttribute($productAttribute);
        }
        return $this;
    }

    /**
     * Load attribute prices information
     *
     * @return Oro_Configurable_Model_Resource_Product_Type_Configurable_Attribute_Collection
     */
    protected function _loadPrices()
    {
        if ($this->count()) {
            $pricings = array(
                0 => array()
            );

            if ($this->getHelper()->isPriceGlobal()) {
                $websiteId = 0;
            } else {
                $websiteId = (int)Mage::app()->getStore($this->getStoreId())->getWebsiteId();
                $pricing[$websiteId] = array();
            }

            $select = $this->getConnection()->select()
                ->from(array('price' => $this->_priceTable))
                ->where('price.product_super_attribute_id IN (?)', array_keys($this->_items));

            if ($websiteId > 0) {
                $select->where('price.website_id IN(?)', array(0, $websiteId));
            } else {
                $select->where('price.website_id = ?', 0);
            }

            $query = $this->getConnection()->query($select);

            while ($row = $query->fetch()) {
                $pricings[(int)$row['website_id']][] = $row;
            }

            $values = array();

            $associatedProducts = $this->getProduct()->getTypeInstance(true)->getUsedProducts(null, $this->getProduct());
            foreach ($this->_items as $item) {
               $productAttribute = $item->getProductAttribute();
               if (!($productAttribute instanceof Mage_Eav_Model_Entity_Attribute_Abstract)) {
                   continue;
               }

               $usedOptions = $this->_getUsedOptions($associatedProducts, $productAttribute->getAttributeCode());
               $options = Mage::helper('oro_configurable')->getAttributeOptions($productAttribute, $usedOptions);

               foreach ($options as $option) {
                   foreach ($associatedProducts as $associatedProduct) {
                        if (!empty($option['value'])
                            && $option['value'] == $associatedProduct->getData(
                                                        $productAttribute->getAttributeCode())) {
                            // If option available in associated product
                            if (!isset($values[$item->getId() . ':' . $option['value']])) {
                                // If option not added, we will add it.
                                $values[$item->getId() . ':' . $option['value']] = array(
                                    'product_super_attribute_id' => $item->getId(),
                                    'value_index'                => $option['value'],
                                    'label'                      => $option['label'],
                                    'default_label'              => $option['label'],
                                    'store_label'                => $option['label'],
                                    'is_percent'                 => 0,
                                    'pricing_value'              => null,
                                    'use_default_value'          => true
                                );
                            }
                        }
                   }
               }
            }

            foreach ($pricings[0] as $pricing) {
                // Addding pricing to options
                $valueKey = $pricing['product_super_attribute_id'] . ':' . $pricing['value_index'];
                if (isset($values[$valueKey])) {
                    $values[$valueKey]['pricing_value']     = $pricing['pricing_value'];
                    $values[$valueKey]['is_percent']        = $pricing['is_percent'];
                    $values[$valueKey]['value_id']          = $pricing['value_id'];
                    $values[$valueKey]['use_default_value'] = true;
                }
            }

            if ($websiteId && isset($pricings[$websiteId])) {
                foreach ($pricings[$websiteId] as $pricing) {
                    $valueKey = $pricing['product_super_attribute_id'] . ':' . $pricing['value_index'];
                    if (isset($values[$valueKey])) {
                        $values[$valueKey]['pricing_value']     = $pricing['pricing_value'];
                        $values[$valueKey]['is_percent']        = $pricing['is_percent'];
                        $values[$valueKey]['value_id']          = $pricing['value_id'];
                        $values[$valueKey]['use_default_value'] = false;
                    }
                }
            }

            foreach ($values as $data) {
                $this->getItemById($data['product_super_attribute_id'])->addPrice($data);
            }
        }
        return $this;
    }

    /**
     * Get configurable attribute options
     *
     * @param Mage_Catalog_Model_Resource_Product_Type_Configurable_Product_Collection $products
     * @param string $attributeCode
     * @return array
     */
    protected function _getUsedOptions($products, $attributeCode)
    {
        $options = array();
        foreach ($products as $product) {
            $options[] = $product->getData($attributeCode);
        }
        return $options;
    }
}

