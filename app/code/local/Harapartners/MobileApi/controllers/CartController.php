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

class Harapartners_MobileApi_CartController extends Mage_Core_Controller_Front_Action{
    
    protected static $base_url = 'https://api.totsy.com';
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }
    
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }
    public function indexAction()
    {
        try{
            $cart = $this->_getCart();
            if($cart->getQuote()->getItemsCount()){
                $cart->init();
                $cart->save();
                
                if(!$this->_getQuote()->validateMinimumAmount()){
                    $warning = Mage::getStoreConfig('sales/minimum_order/description');
                }
            }
            $itemsArray = array();
            foreach($this->_getQuote()->getAllItems() as $item){
                $link = array(
                        'rel'        =>    'self',
                        'href'        =>    self::$base_url . '/product/' . $item->getProductId(),
                );
                $items = array(
                        'name'        =>    $item->getName(),
                        'quantity'    =>    $item->getQty(),
                        'link'        =>    $link,
                );
                $itemsArray[] = $items;
            }
            
            $user_link = array(
                    'rel'    =>    'self',
                    'href'    =>    self::$base_url . '/customer/' . $this->_getQuote()->getCustomerId(), 
            );
            $user = array(
                    'link'    =>    $user_link,        
            );
            
            
            $link = array(
                    'rel'    =>    'self',
                    'href'    =>    self::$base_url . '/cart/' . $this->_getQuote()->getId(),
            );
            
            $body =  array(
                    'user'        =>    $user,
                    'items'        =>    $itemsArray,
                    'link'        =>    $link,
            );
            echo json_encode($body);
            
        }catch(Exception $e){
            
        }    
    }

    public function updateAction(){
        
    }

    public function deleteAction(){
        
    }


}