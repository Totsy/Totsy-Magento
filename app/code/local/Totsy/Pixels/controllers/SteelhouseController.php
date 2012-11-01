<?php
class Totsy_Pixels_SteelhouseController extends Mage_Core_Controller_Front_Action
{
    public function indexAction() { 
        if($this->getRequest()->getParam('product') && $this->getRequest()->getParam('category')) {
            if(($_product = $this->getRequest()->getParam('product'))
            && ($_category = $this->getRequest()->getParam('category'))) {             
                $_product = $this->getRequest()->getParam('product');
                $_category = $this->getRequest()->getParam('category');
                $_product = Mage::getModel('catalog/product')->load($_product);
                $_category = Mage::getModel('catalog/category')->load($_category);
                if(($_product && $_product->getId()) && $_category && $_category->getId()) {
                    Mage::register('current_product',$_product);
                    Mage::register('current_category',$_category);
                }
            } 
        }
        
        /*
        if( $this->getRequest()->getParam('sales_products')) {           	
            if($_sales_products = $this->getRequest()->getParam('sales_products')) {
                Mage::register('current_sales_products', $_sales_products);
            } 
        } */
        
        if($this->getRequest()->getParam('current_url')) {
            if($current_url = $this->getRequest()->getParam('current_url')) {
                Mage::register('current_url',base64_decode($current_url));
            }
        }
        
        if($this->getRequest()->getParam('referrer_url')) {
            if($referrer_url = $this->getRequest()->getParam('referrer_url')) {
                Mage::register('referrer_url',base64_decode($referrer_url));
            }
        }
        $this->loadLayout()->renderLayout();
    }

    public function headerAction(){
        $this->loadLayout()->renderLayout();
    }
}