<?php

/**
 * @category    Totsy
 * @package     Totsy_CatalogInventory_Model_Stock
 * @author      Tom Royer <skosh@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_CatalogInventory_Model_Stock extends Mage_CatalogInventory_Model_Stock {

    /**
     * Subtract product qtys from stock.
     * Return array of items that require full save
     *
     * @param array $items
     * @return array
     */
    public function registerProductsSale($items)
    {
        $qtys = $this->_prepareProductQtys($items);
        $item = Mage::getModel('cataloginventory/stock_item');
        $this->_getResource()->beginTransaction();
        $stockInfo = $this->_getResource()->getProductsStock($this, array_keys($qtys), true);
        $fullSaveItems = array();
        foreach ($stockInfo as $itemInfo) {
            $item->setData($itemInfo);
            if (!$item->checkQty($qtys[$item->getProductId()])) {
                $this->_getResource()->commit();
                $product = Mage::getModel('catalog/product')->load($item->getProductId());
                Mage::throwException(Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', $product->getName()));
            }
            $item->subtractQty($qtys[$item->getProductId()]);
            if (!$item->verifyStock() || $item->verifyNotification()) {
                $fullSaveItems[] = clone $item;
            }
        }
        $this->_getResource()->correctItemsQty($this, $qtys, '-');
        $this->_getResource()->commit();
        return $fullSaveItems;
    }
}