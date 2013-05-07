<?php

class Totsy_Sailthru_Helper_Item {

    public function prepare($item) {

        $id = $item->getSku();
        $title = $item->getName();
        $sku = $item->getSku();
        $price = $item["product"]->getFinalPrice()*100;
        $qty = $item->getQty();
        $url = $item["product"]->getProductUrl();
        $tags = array();

        $product = Mage::getResourceModel('catalog/product_collection')
            ->addFieldToFilter('entity_id', $item->getProductId())
            ->addAttributeToSelect('departments')
            ->addAttributeToSelect('ages')
            ->getFirstItem();

        $departments = $product->getAttributeText('departments');
        $ages = $product->getAttributeText('ages');

        unset($product);

        if (!empty($departments)){
            if (!is_array($departments)){
                $departments = array( $departments );
            } 
            $tags= array_merge($tags,$departments);
        }
        if (!empty($ages)){
            if (!is_array($ages)){
                $ages = array( $ages );
            }
            $tags= array_merge($tags,$ages);
        }

        if (!empty($tags)){
            array_unique($tags);
            $tags = implode(', ', $tags);
        } else {
            $tags = '';
        }
        
        $title = isset($title)?$title:$sku;    

        $vars['event_id'] = Mage::getModel('catalog/product')
            ->load( $item->getProduct()->getEntityId())
            ->getCategoryIds();

        return compact('qty', 'title', 'price', 'id', 'url', 'tags', 'vars');
    }

}
