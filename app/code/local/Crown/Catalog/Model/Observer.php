<?php
/**
 *
 * @category 	Crown
 * @package 	Crown_Catalog
 * @since 		1.0.0
 */
class Crown_Catalog_Model_Observer {

    /**
     * Reindex category products when product list changes are saved
     * @since 1.0.0
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function categoryProductReindexAfterSave($observer) {

        $data = $observer->getEvent()->getData();

        if ($data['category']['is_changed_product_list']) {
            /* @var Mage_Catalog_Model_Category $category */
            $category = $data['data_object'];
            try {
                Mage::getSingleton('index/indexer')->processEntityAction(
                    $category, $category::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
                );
            } catch (Exception $e) {
                Mage::log('Error: ' . $e->getMessage());
            }
        }
    }
}