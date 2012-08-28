<?php

class Totsy_CatalogInventory_Model_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item {

    public function save(){
        $return = parent::save();
        if ($return!==false){
            Mage::helper('product/outofstock')->adminAfterSaveUpdateQty($this);
        }
        return $return;
    }

}