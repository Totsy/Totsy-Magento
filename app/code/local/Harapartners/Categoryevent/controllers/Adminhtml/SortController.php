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
 
class Harapartners_Categoryevent_Adminhtml_SortController extends Mage_Adminhtml_Controller_Action{
    
    public function indexAction(){
        $this->_title($this->__('Category Event'))->_title($this->__('Sort Category Events'));
        $sortDate = $this->getRequest()->getPost('sort_date');
        if(!$sortDate){
            $sortDate = now();
        }
        $storeId = $this->getRequest()->getPost('sort_store');
        if(!$storeId){
            $storeId = Mage_Core_Model_App::DISTRO_STORE_ID;
        }
        if (!!$this->getRequest()->getPost('post_active') ) {
            Mage::getSingleton('adminhtml/session')->setData('categoryevent_sort_data_post', true); 
            try {            
                $sortentry = Mage::getModel('categoryevent/sortentry')->loadByDate($sortDate, $storeId, false);
                $arrayLive = json_decode($sortentry->getData('live_queue'), true);
                $arrayUpcoming = json_decode($sortentry->getData('upcoming_queue'), true);
                Mage::getSingleton('adminhtml/session')->setData('categoryevent_sort_date', $sortDate);
                Mage::getSingleton('adminhtml/session')->setData('categoryevent_sort_storeid', $storeId);
                Mage::getSingleton('adminhtml/session')->setData('categoryevent_sort_live_queue', $arrayLive);
                Mage::getSingleton('adminhtml/session')->setData('categoryevent_sort_upcoming_queue', $arrayUpcoming); 
            }catch (Exception $e){
                Mage::logException($e);
                Mage::getSingleton('core/session')->addError('Cannot Load Events');
                Mage::getSingleton('adminhtml/session')->setData('categoryevent_sort_data_post', false);
            }
        }else {
            Mage::getSingleton('adminhtml/session')->setData('categoryevent_sort_data_post', false);
        }
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function sortSaveAction(){
//        $post = $this->getRequest()->getPost();
        $liveSortedIdArray = array();
        $upComingSortedIdArray = array();
        $liveSortedIdArray = $this->getRequest()->getPost('recordsLiveArray');
        $upComingSortedIdArray = $this->getRequest()->getPost('recordsUpArray');
        if(!!$this->getRequest()->getPost('sortdate')){
            $sortDate = $this->getRequest()->getPost('sortdate');
        }else {
            $sortDate = now();
        }
        if(!!$this->getRequest()->getPost('storeid')){
            $storeId = $this->getRequest()->getPost('storeid');
        }else {
            $storeId = Mage_Core_Model_App::DISTRO_STORE_ID;
        }
        try {
            $sortentry = Mage::getModel('categoryevent/sortentry')->loadByDate($sortDate, $storeId, false);
            Mage::getModel('categoryevent/sortentry')->saveUpdateSortCollection($liveSortedIdArray, $upComingSortedIdArray, $sortentry);   
            $jsonResponse['status'] = 1;
            $jsonResponse['error_message'] = '';         
        }catch (Exception $e){
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError('Cannot Save Sort');
            $jsonResponse['status'] = 0;
            $jsonResponse['error_message'] = 'Cannot Save Sort';
        }
        echo json_encode($jsonResponse);
        exit;
    }
    
    public function sortRebuildAction(){
//        $post = $this->getRequest()->getPost();
        if(!!$this->getRequest()->getPost('sortdate')){
            $sortDate = $this->getRequest()->getPost('sortdate');
        }else {
            $sortDate = now();
        }
        if(!!$this->getRequest()->getPost('storeid')){
            $storeId = $this->getRequest()->getPost('storeid');
        }else {
            $storeId = Mage_Core_Model_App::DISTRO_STORE_ID;
        }
        try {
            Mage::getModel('categoryevent/sortentry')->rebuildSortCollection($sortDate, $storeId);
            $jsonResponse['status'] = 1;
            $jsonResponse['error_message'] = '';     
        }catch (Exception $e){
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError('Rebuild Faild');
            $jsonResponse['status'] = 0;
            $jsonResponse['error_message'] = 'Rebuild Faild';
        }
        echo json_encode($jsonResponse);
        exit;
    }
}