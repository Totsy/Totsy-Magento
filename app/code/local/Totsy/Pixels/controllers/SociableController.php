<?php
class Totsy_Pixels_SociableController extends Mage_Core_Controller_Front_Action
{
    public function productAction(){
        if(($_product = $this->getRequest()->getParam('product'))
        && ($_category = $this->getRequest()->getParam('category'))) {
            $_product = Mage::getModel('catalog/product')->load($_product);
            $_category = Mage::getModel('catalog/category')->load($_category);
            if(($_product && $_product->getId())
            && $_category && $_category->getId()) {
                Mage::register('current_product',$_product);
                Mage::register('current_category',$_category);

                $this->loadLayout()->renderLayout();
            }
        }
    }

    public function headerAction(){
        $this->loadLayout()->renderLayout();
    }
}