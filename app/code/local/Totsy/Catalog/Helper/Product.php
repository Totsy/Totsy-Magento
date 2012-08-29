<?php

/**
 * @category    Totsy
 * @package     Totsy_Outofstock_Helper_Product
 * @author      Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Catalog_Helper_Product extends Mage_Core_Helper_Abstract {


    /**
    * Updates all configurable products qty in specified category/event
    *   
    * @param int category_id
    * @return void
    */
    public function adminAfterImportProduct($category_id = 0){

        $model = Mage::getModel('catalog/category');
        $event = $model->load($category_id);

        $layer = Mage::getSingleton('catalog/layer');
        $layer->setCurrentCategory($event);
        $products = $layer->getProductCollection()->load()->toArray();
        
        foreach ($products as $p){
        	$product = Mage::getModel('catalog/product')->load($p['id']);
        	$this->updateConfigurableProductQty($product);
        }
    }

    /**
    * Before updating a configurable product, this methos makes sure
    * that item that is going to be updated is configurabe, and then updates the item. 
    *   
    * @param int/object $item
    * @return void
    */
    public function adminAfterSaveUpdateQty($item){
        $item_id = 0;
        if (is_object($item)){
            $item_id = $item->getProductId();
        } else if (is_numeric($item)){
            $item_id = $item;
        }
        $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                       		->getParentIdsByChild($item->getProductId());
        if (!empty($parentIds) && !empty($parentIds[0])){
        	$product = Mage::getModel('catalog/product')->load($parentIds[0]);                
       		$this->updateConfigurableProductQty($product);
       	}
    }

    /**
    * Updates given configurable product qty
    *   
    * @param object $product
    * @return void
    */   
	public function updateConfigurableProductQty($product){
        if (!$product->isConfigurable()){
            return;
        }
        $collection = $product->getCollection()->getConnection();
        $collection->raw_query("
            UPDATE cataloginventory_stock_item as si
            JOIN (
                SELECT sum(`csi`.`qty`) as 'total', `cpr`.parent_id as 'pid' 
                FROM cataloginventory_stock_item csi
                INNER JOIN `catalog_product_relation` cpr ON `cpr`.`child_id`=`csi`.`product_id`
                WHERE `cpr`.parent_id='".$product->getId()."' 
            ) as s ON s.pid=si.product_id
            SET si.qty = s.total
        ");
    }
}
?>