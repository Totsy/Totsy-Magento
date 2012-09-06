<?php
/**
 * @category    Totsy
 * @package     Totsy_Catalog_Block
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Catalog_Block_Product_View_Type_Configurable
    extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    protected function _validateAttributeValue($attributeId, &$value, &$options)
    {
        if(isset($options[$attributeId][$value['value_index']])) {
            $category = Mage::registry('current_category');
            $products = $options[$attributeId][$value['value_index']];

            // ensure that this product is associated with the current event
            foreach ($products as $product) {
                $eventProducts = $category->getProductCollection();
                foreach ($eventProducts as $eventProduct) {
                    if ($product == $eventProduct->getId()) {
                        return true;
                    }
                }

            }

            return false;
        }

        return false;
    }
}
