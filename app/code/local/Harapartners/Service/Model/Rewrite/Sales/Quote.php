<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Service_Model_Rewrite_Sales_Quote
    extends Mage_Sales_Model_Quote
{
    public function addProductAdvanced(
        Mage_Catalog_Model_Product $product,
        $request = null,
        $processMode = null
    ) {
        // Harapartners, Jun
        // check if product is salable due to category/event limit,
        // frontend only
        if (!Mage::app()->getStore()->isAdmin() &&
            !$product->isSalable() &&
            true !== Mage::registry('order_import_ignore_stockcheck')
        ) {
            Mage::throwException(
                sprintf(
                    'The selected item \'%s\' is not available.',
                    $product->getName()
                )
            );
        }

        // Harapartners, Jun
        // Virtual product (coupons) should have qty=1 per line item
        // (code reservation logic)
        if ($product->isVirtual()) {
            // No qty default to 1
            if ($request->getQty() && $request->getQty() != 1) {
                $request->setQty(1);
                Mage::getSingleton('checkout/session')->addNotice(
                    'Your coupon purchase is reserved, and the quantity has ' .
                    'been adjusted to 1.'
                );
            }
        }

        return parent::addProductAdvanced($product, $request, $processMode);
    }

    /**
     * Adding catalog product object data to quote.
     * Refuse to add new virtual products, so that a new line item is created.
     *
     * @param   Mage_Catalog_Model_Product $product
     * @param   int $qty
     * @return  Mage_Sales_Model_Quote_Item
     */
    protected function _addCatalogProduct(
        Mage_Catalog_Model_Product $product,
        $qty = 1
    ) {
        $newItem = false;
        if ($product->isVirtual()) {
            $item = null;
        } else {
            $item = $this->getItemByProduct($product);
        }

        if (!$item) {
            $item = Mage::getModel('sales/quote_item');
            $item->setQuote($this);
            if (Mage::app()->getStore()->isAdmin()) {
                $item->setStoreId($this->getStore()->getId());
            }
            else {
                $item->setStoreId(Mage::app()->getStore()->getId());
            }
            $newItem = true;
        }

        /**
         * We can't modify existing child items
         */
        if ($item->getId() && $product->getParentProductId()) {
            return $item;
        }

        $item->setOptions($product->getCustomOptions())
            ->setProduct($product);

        // Add only item that is not in quote already (there can be other new
        // or already saved item
        if ($newItem) {
            $this->addItem($item);
        }

        return $item;
    }
}
