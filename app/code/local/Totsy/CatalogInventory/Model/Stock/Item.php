<?php

/**
 * @category    Totsy
 * @package     Totsy_CatalogInventory_Model_Stock_Item
 * @author      Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_CatalogInventory_Model_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item {

    public function save(){
        $return = parent::save();
        if ($return!==false){
            Mage::helper('product/outofstock')->adminAfterSaveUpdateQty($this);
        }
        return $return;
    }

}