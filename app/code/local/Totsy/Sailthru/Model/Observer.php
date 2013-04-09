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

        $allItems = $cart->getQuote()->getAllVisibleItems();
        foreach ($allItems as $ai) {
            $items[] = Mage::helper('sailthru/item')->prepare($ai);
        }

        $this->_callPurchaseApi(
            array(
                'email' => Mage::getSingleton('customer/session')->getCustomer()->getEmail(),
                'items' => $items,
                'incomplete' => 1 //0: complete ; 1: imcomplete
            )
        );
    }

    public function removeItemFromIncompleteOrder(Varien_Event_Observer $observer)
    {
        $item = $observer->getItem();
        $cart = $observer->getCart();
        $items = array();
        
        if (!isset($item) || empty($item)) {
            return;
        }

        // Add other items from cart to 
        // sailthru incomplete order
        $allItems = $cart->getQuote()->getAllVisibleItems();
        foreach ($allItems as $ai) {

            if ($item == $ai->getId()){
                continue;
            }

            $items[] = Mage::helper('sailthru/item')->prepare($ai);;
        }

        $this->_callPurchaseApi(array(
            'email' => Mage::getSingleton('customer/session')->getCustomer()->getEmail(), 
            'items' => $items,
            'incomplete' => 1 //0: complete ; 1: imcomplete
        )); 

    }

    protected function _callPurchaseApi($data)
    {
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

}