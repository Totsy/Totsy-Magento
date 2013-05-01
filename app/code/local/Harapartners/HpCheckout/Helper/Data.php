<?php 
class Harapartners_HpCheckout_Helper_Data extends Mage_Core_Helper_Abstract {

    //For cart timer
    public function getCurrentTime() {
        $defaultTimezone = date_default_timezone_get();
        $mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);            
        date_default_timezone_set($mageTimezone);
        $timer = now();
        date_default_timezone_set($defaultTimezone);
        
        return strtotime($timer);
    }

    /**
     * Get concatenated category names based on order items
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function getCategoryNames($productId)
    {
        $categories = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect('name')
            ->joinField('product_id',
                'catalog/category_product',
                'product_id',
                'category_id = entity_id',
                null)
            ->addFieldToFilter('product_id', $productId);

        $result = array();
        foreach ($categories as $category) {
            $result[] = $category->getName();
        }

        return implode(',', $result);
    }
	
    public function convertArrayToLittleHash($paymentArray){
        foreach($paymentArray as $key => $infos) {
            $newKey = lcfirst(str_replace(' ','', ucwords(str_replace('_',' ',$key))));
            $hash[$newKey] = $infos;
        }
        return $hash;
    }
}