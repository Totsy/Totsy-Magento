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

class Harapartners_MobileApi_EventController extends Mage_Core_Controller_Front_Action{
    
    public function indexAction(){
        $params = $this->getRequest()->getParams();
        $response = Mage::app()->getResponse();
        $response->setHeader('Content-type', 'application/json', true);
        try{
            if(count($params) == 0 || isset($params['categories']) || isset($params['ages']) || isset($params['departments']) || isset($params['when'])){ 
                // a colleciton active events
                $categories = isset($params['categories']) ? $params['categories'] : null;
                $ages = isset($params['ages']) ? $params['ages'] : null;
                $departments = isset($params['departments']) ? $params['departments'] : null;
                $when = isset($params['when']) ? $params['when'] : null;
                    
                $collection = $this->_getEventCollection($categories, $departments, $ages, $when);
             
                $tree = array();
                foreach($collection->getItems() as $event){
                    $tree[] = Mage::helper('mobileapi')->getEventInfo($event->getId());
                }
                $result = $tree;
            }elseif(count($params) == 2 && isset($params['id']) && !! $params['id'] && isset($params['size']) && !! $params['size']){
                // a single sale Event instance
                $eventId = $params['id'];
                $size = $params['size'];
                $event = Mage::getModel('catalog/category')->load($eventId);
                
                $baseImageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/category/';
                switch(strtolower($size)){
                    case 'small':
                        $img = $baseImageUrl . $event->getData('thumbnail');
                        break;
                    case 'med':
                        $img = $baseImageUrl . $event->getData('small_image');
                        break;
                    case 'large':
                        $img = $baseImageUrl . $event->getData('image');
                        break;
                }
                $result = $img;
                $response->setHeader('Accept', 'image/png, image/gif, image/jpeg, image/jpg', true);
            }elseif(count($params)==1 && isset($params['id']) && !! $params['id']){
                $eventId = $params['id'];
                $event = Mage::helper('mobileapi')->getEventInfo($eventId);
                $result = $event;
            }else{
                $response->setHttpResponseCode(400); 
                Mage::throwException($this->__("Invalid Request"));
            }
        }catch(Exception $e){
            $result = $e->getMessage();
        }
        $response->setBody(json_encode($result));
        
    }
    
    public function productAction()
    {
        $params = $this->getRequest()->getParams();
        $response = Mage::app()->getResponse();
        $response->setHeader('Content-type', 'application/json', true);
        try{
            if(count($params) == 2 && isset($params['id']) && !!$params['id']){
                // Products that are part of an Event
                $eventId = $params['id'];
                $event = Mage::getModel('catalog/category')->load($eventId);
                $collection = $event->getProductCollection();
                $resultArray = array();
                $productModel = Mage::getModel('catalog/product');
                foreach($collection as $product){
                    $product = $productModel->load($product->getId());
                    $resultArray[] = Mage::helper('mobileapi')->getProductInfo($product);
                }
                $result = $resultArray;
            }else{
                Mage::throwException($this->__("Invalid Request"));
            }
        }catch(Exception $e){
            $result = $e->getMessage();
        }
        $response->setBody(json_encode($result));
    }
    
    protected function _getEventCollection($categories=null, $departments=null, $ages=null, $when=null)
    {
        if(!!$categories && !is_array($categories)){
            $categories = array($categories);
        }
        if(!!$ages && !is_array($ages)){
            $ages = array($ages);
        }
        if(!!$departments && !is_array($departments)){
            $departments = array($departments);
        }
        switch($when){
            case 'current':
                $startTimeFilter = array('to' => date('Y-m-d H:i:s'));
                $endTimeFilter = array('from' => date('Y-m-d H:i:s'));
                break;
            case 'past':
                $endTimeFilter =array('to' => date('Y-m-d H:i:s'));
                $startTimeFilter = $endTimeFilter;
                break;
            case 'upcoming':
                $startTimeFilter = array('from' => date('Y-m-d H:i:s'));
                $endTimeFilter = $startTimeFilter;
                break;
            default:
                $startTimeFilter = array('notnull' => 1);
                $endTimeFilter = array('notnull' => 1);
        }
        $categoryFilter = Mage::helper('mobileapi')->setFilter($categories[0], 'categories');
        $ageFilter = Mage::helper('mobileapi')->setFilter($ages[0], 'ages');
        $departmentFilter = Mage::helper('mobileapi')->setFilter($departments[0], 'departments');
        
        $collection = Mage::getModel('catalog/category')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('children_count', array('eq' => 0))
                    ->addAttributeToFilter('is_active', '1')
                    ->addAttributeToFilter($ageFilter)
                    ->addAttributeToFilter($departmentFilter)
                    ->addAttributeToFilter('event_start_date', $startTimeFilter)
                    ->addAttributeToFilter('event_end_date', $endTimeFilter);    
        
        
        return $collection;
    }
    
}