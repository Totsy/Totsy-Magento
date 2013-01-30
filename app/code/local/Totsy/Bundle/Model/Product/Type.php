<?php
class Totsy_Bundle_Model_Product_Type extends Mage_Bundle_Model_Product_Type
{
    /**
     * Check if product can be bought
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Bundle_Model_Product_Type
     * @throws Mage_Core_Exception
     */
    public function checkProductBuyState($product = null)
    {
        parent::checkProductBuyState($product);
        $product            = $this->getProduct($product);
        $productOptionIds   = $this->getOptionsIds($product);
        $productSelections  = $this->getSelectionsCollection($productOptionIds, $product);
        $selectionIds       = $product->getCustomOption('bundle_selection_ids');
        $selectionIds       = unserialize($selectionIds->getValue());
        $buyRequest         = $product->getCustomOption('info_buyRequest');
        $buyRequest         = new Varien_Object(unserialize($buyRequest->getValue()));
        $bundleOption       = $buyRequest->getBundleOption();

        if (empty($bundleOption)) {
            Mage::throwException($this->getSpecifyOptionMessage());
        }

        //2012-11-14 - CJD - removing inventory check due to cart reservation logic

        $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);
        $optionsCollection = $this->getOptionsCollection($product);
        foreach ($optionsCollection->getItems() as $option) {
            if ($option->getRequired() && empty($bundleOption[$option->getId()])) {
                Mage::throwException(
                    Mage::helper('bundle')->__('Required options are not selected.')
                );
            }
        }

        return $this;
    }
}
