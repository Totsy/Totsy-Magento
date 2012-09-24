<?php

/**
 * @category    Totsy
 * @package     Totsy_CatalogInventory_Model_Stock_Status
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_CatalogInventory_Model_Stock_Status extends Mage_CatalogInventory_Model_Stock_Status {

    public function syncStatusWithStock($order)
    {
        //Sync Item Stock with Item Stock Status
        foreach($order->getItemsCollection() as $item) {
            $indexerStock = Mage::getModel('cataloginventory/stock_status');
            $indexerStock->updateStatus($item->getProductId());
            //Make Sure that parent product status stay 1
            $configurableProductModel = Mage::getModel('catalog/product_type_configurable');
            $parentIds = $configurableProductModel->getParentIdsByChild($item->getProductId());
            if ($parentIds) {
                foreach ($parentIds as $parentId) {
                    $stockStatus = Mage::getModel('cataloginventory/stock_status')->load($parentId,'product_id');
                    $stockStatus->setData('stock_status','1')
                        ->save();
                }
            }
        }
    }

}