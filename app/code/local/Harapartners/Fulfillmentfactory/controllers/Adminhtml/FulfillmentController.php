<?php
class Harapartners_Fulfillmentfactory_Adminhtml_FulfillmentController extends Mage_Adminhtml_Controller_action {

	/**
	 * Used to select the new fulfilment type for all products in a category.
	 * @since 0.5.0
	 * @return void
	 */
    public function indexAction() {
        $categoryId = $this->getRequest ()->getParam ( 'category_id' );
        $category = Mage::getModel ( 'catalog/category' )->load ( $categoryId );

        if (! ! $category && ! ! $category->getId ()) {
            $this->loadLayout()
                ->_addContent($this->getLayout()->createBlock('fulfillmentfactory/adminhtml_fulfillment_edit'))
                ->renderLayout();
        } else {
            Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'fulfillmentfactory' )->__ ( 'Invalid Category ID' ) );
            $this->_redirect ( 'adminhtml/catalog_category/edit');
            return;
        }
    }

    /**
     * @since 0.5.0
     * @return void
     */
    public function saveAction() {
        $categoryId = $this->getRequest()->getParam('category_id');
        $category = Mage::getModel ( 'catalog/category' )->load ( $categoryId );

        $fulfillmentType = $this->getRequest()->getParam('fulfillment_type');

        $productIds = array();
        foreach ($category->getProductCollection() as $product) {
            $productIds[] = $product->getId();

            // Set stock qty to 0
            $stockItem = Mage::getModel ( 'cataloginventory/stock_item' )->loadByProduct ( $product->getId() );
            $stockItem->setData ( 'qty', 0 );
            $stockItem->save ();

            // Get child products too
            $_productType = $product->getType();
            if( Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE == $_productType ) {
                if( is_array($product->getChildrenIds()) ) {
                    foreach ($product->getChildrenIds() as $childId) {
                        $productIds[] = $childId;

                        // Set stock qty to 0
                        $stockItem = Mage::getModel ( 'cataloginventory/stock_item' )->loadByProduct ( $childId );
                        $stockItem->setData ( 'qty', 0 );
                        $stockItem->save ();
                    }
                }
            }
        }

        $attributeHelper = Mage::helper('adminhtml/catalog_product_edit_action_attribute');

        $storeId = $attributeHelper->getSelectedStoreId();

        $attributesData = array(
            'fulfillment_type'  => $fulfillmentType
        );

        Mage::getSingleton('catalog/product_action')
            ->updateAttributes($productIds, $attributesData, $storeId);

        Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( count($productIds) . ' ' . Mage::helper ( 'fulfillmentfactory' )->__ ( 'Products Update' ) );

        $this->_redirect ( 'adminhtml/catalog_category/edit');
        return;
    }
}