<?php
/**
 * @category    Totsy
 * @package     Totsy_CatalogFeed_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

class Totsy_CatalogFeed_Model_Feed_Csv_Insparq
    extends Totsy_CatalogFeed_Model_Feed_Csv
{
    /**
     * Process an event for this feed.
     *
     * @param Mage_Catalog_Model_Category $event
     *
     * @return void
     */
    protected function _processEvent(Mage_Catalog_Model_Category $event)
    {
    }

    /**
     * Process a product for this feed.
     *
     * @param Mage_Catalog_Model_Category $event
     * @param Mage_Catalog_Model_Product  $product
     * @param Mage_Catalog_Model_Product  $parent
     *
     * @return void
     */
    protected function _processProduct(
        Mage_Catalog_Model_Category $event,
        Mage_Catalog_Model_Product $product,
        Mage_Catalog_Model_Product $parent = null
    ) {
        // compile the departments as a user-friendly label
        $dept = (array) $product->getAttributeText('departments');
        array_walk($dept, function(&$value) {
                $departmentLabels = array(
                    "boys-apparel" => "Boys Apparel",
                    "girls-apparel" => "Girls Apparel",
                    "shoes" => "Shoes",
                    "accessories" => "Accessories",
                    "toys-books" => "Toys and Books",
                    "gear" => "Gear",
                    "home" => "Home",
                    "moms_dads" => "Moms and Dads"
                );

                if (isset($departmentLabels[$value])) {
                    $value = $departmentLabels[$value];
                }
            });

        // compile the ages as a user-friendly label
        $age = (array) $product->getAttributeText('ages');
        array_walk($age, function(&$value) {
                $ageLabels = array(
                    "newborn" => "Newborn 0-6M",
                    "infant" => "Infant 6-24M",
                    "toddler" => "Toddler 1-3Y",
                    "preschool" => "Preschool 3-4Y",
                    "school" => "School Age 5+",
                    "adult" => "Adult"
                );

                if (isset($ageLabels[$value])) {
                    $value = $ageLabels[$value];
                }
            });

        // determine the product image URL
        $imageUrl = (string) Mage::helper('catalog/image')->init($product, 'small_image');

        // determine the product description
        $description = $product['description'];
        if ($parent !== null) {
            $description = $parent['description'];
        }

        $feedItem = array(
            $product['entity_id'],
            (null !== $parent) ? $parent->getId() : '',
            $product['name'],
            preg_replace("/[\r\n]/", "", strip_tags($description)),
            implode(',', $dept),
            $product->getProductUrl(),
            $imageUrl,
            $product['price'],
            $product['special_price'],
            implode(',', $age),
            $product->getAttributeText('color'),
            $product->getAttributeText('size')
        );

        fputcsv($this->_handle, $feedItem);
    }
}
