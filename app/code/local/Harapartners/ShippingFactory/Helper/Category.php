<?php 
class Harapartners_ShippingFactory_Helper_Category {
    
    public function getCategory($uniqueCategoryName) {
        $categoryModel = Mage::getModel ( 'catalog/category' );
        $category = $categoryModel->getCollection ()->addAttributeToFilter ( 'name', $uniqueCategoryName )->getFirstItem ();
        if ($category && $category->getId ()) {
            return $category->getId ();
        } else {
            return null;
        }
    }
}