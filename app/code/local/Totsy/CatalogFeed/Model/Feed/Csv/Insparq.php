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
    protected function _formatFeedItem(
        Mage_Catalog_Model_Category $event,
        Mage_Catalog_Model_Product $child,
        Mage_Catalog_Model_Product $parent = null)
    {
        // compile the departments as a user-friendly label
        $dept = (array) $child->getAttributeText('departments');
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
        $age = (array) $child->getAttributeText('ages');
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
        $imageUrl = (string) Mage::helper('catalog/image')->init($child, 'small_image');

        // determine the product description
        $description = $child['description'];
        if ($parent !== null) {
            $description = $parent['description'];
        }

        return array(
            $child['entity_id'],
            (null !== $parent) ? $parent->getId() : '',
            $child['name'],
            preg_replace("/[\r\n]/", "", strip_tags($description)),
            implode(',', $dept),
            $child->getProductUrl(),
            $imageUrl,
            $child['price'],
            $child['special_price'],
            implode(',', $age),
            $child->getAttributeText('color'),
            $child->getAttributeText('size')
        );
    }
}
