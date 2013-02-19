<?php

/*
* I'm aware of Harapartners_EmailFactory_Model_Observer::sailthruPurchasing() 
* is ralated to what i'm trying tot do here, but it's not sending updated 
* cart info to sailthru, and thus we have incorrect purchases data (in Sailthru)
*/

class Totsy_Sailthru_Model_Observer
{

    public function updateIncompleteOrder(Varien_Event_Observer $observer)
    {
        $info = $observer->getInfo();
        $cart = $observer->getCart();
        $items = array();
        
        if (!isset($info) || empty($info) || !is_array($info)) {
            return;
        }

        foreach ($info as $id=>$qty) {
            $itemQuoted = $cart->getQuote()->getItemById($id);
            $itemInfo = Mage::getModel('catalog/product')->load($itemQuoted->getProductId());
            $items[] = $this->_preSailthruPurchase($itemInfo, $qty);
        }

        $data = array(
            'email' => Mage::getSingleton('customer/session')->getCustomer()->getEmail(), 
            'items' => $items,
            'incomplete' => 1 
        ); //0: complete ; 1: imcomplete
        if (isset($_COOKIE['sailthru_bid'])) {
            $data['message_id'] = $_COOKIE['sailthru_bid'];
        }  
        $queueData = array(
            'call' => array(
                'class' => 'emailfactory/sailthruconfig',
                'methods' => array(
                    'getHandle',
                    'apiPost'
                )
            ),
            'params' => array(
                'apiPost' => array('purchase') + compact('data')
            )
        );
        $queue = Mage::getModel('emailfactory/sailthruqueue');
        $queue->addToQueue($queueData);

    }

    protected function _preSailthruPurchase ($itemInfo, $qty)
    {
        $price = $itemInfo->getSpecialPrice();
        $price = number_format($price, 2);
        $price = $price*100;

        $item = array(
            'id' => $itemInfo->getSku(),
            'url' => $itemInfo->getProductUrl(),
            'title' => $itemInfo->getName(),
            'price' => $price,
            'qty' => $qty['qty']
        );

        return $item;
    }
}