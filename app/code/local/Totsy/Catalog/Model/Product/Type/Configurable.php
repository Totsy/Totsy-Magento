<?php
/**
 * @category    Totsy
 * @package     Totsy_Catalog_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

class Totsy_Catalog_Model_Product_Type_Configurable
    extends Mage_Catalog_Model_Product_Type_Configurable
{
    public function isSalable($product = null)
    {
        $salable = Mage_Catalog_Model_Product_Type_Abstract::isSalable($product);

        if ($salable !== false) {
            $salable = false;
            if (!is_null($product)) {
                $this->setStoreFilter($product->getStoreId(), $product);
            }
            // use an empty array() as the (first) required attributes argument
            // so that no attributes are loaded for children simple products
            foreach ($this->getUsedProducts(array(), $product) as $child) {
                if ($child->isSalable()) {
                    $salable = true;
                    break;
                }
            }
        }

        return $salable;
    }
}
