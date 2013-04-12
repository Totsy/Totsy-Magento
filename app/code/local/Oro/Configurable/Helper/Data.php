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
 * Oro Configurable Helper
 */
class Oro_Configurable_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Loads attribute options by option ids
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param array $optionIds
     * @return array
     */
    public function getAttributeOptions($attribute, $optionIds)
    {
        $collection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setPositionOrder('asc')
            ->setAttributeFilter($attribute->getId())
            ->setStoreFilter($attribute->getStoreId())
            ->addFieldToFilter('main_table.option_id', array('in' => $optionIds))
            ->load();

        return $collection->toOptionArray();
    }

}
