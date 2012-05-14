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
 
class Harapartners_Categoryevent_Block_Adminhtml_Sort_Index_Edit extends Mage_Adminhtml_Block_Widget_Container {
    
       
    public function __construct(){
        parent::__construct();
        $this->setTemplate('categoryevent/sort/index/edit.phtml');
    }

    public function isSingleStoreMode() {
        if (!Mage::app()->isSingleStoreMode()) {
               return false;
        }
        return true;
    }
    
    public function getSortSavePostUrl(){
    	return $this->getUrl('categoryevent/adminhtml_sort/sortsave');
    }
    
    public function getSortRebuildPostUrl(){
    	return $this->getUrl('categoryevent/adminhtml_sort/sortrebuild');
    }
    
    public function getPostKey(){        
        if (!!(Mage::getSingleton('adminhtml/url')->getSecretKey("adminhtml_sort","post"))){            
            return Mage::getSingleton('adminhtml/url')->getSecretKey("adminhtml_sort","post");
        }else {           
            return false;
        }
    }
    
    public function getSortDate(){        
        $sortDate = Mage::getSingleton('adminhtml/session')->getData('categoryevent_sort_date');
        if (!!$sortDate) {
            return $sortDate;
        }else {
            return date("Y-m-d");
        }    
    }
    
    public function getSortStore(){       
        $storeId = Mage::getSingleton('adminhtml/session')->getData('categoryevent_sort_storeid');
        if (!!$storeId) {
            return $storeId;
        }else {
            return $storeId = Mage_Core_Model_App::DISTRO_STORE_ID;
        }
    }
    
    public function loadSortCollection($sortDate,$storeId) {
        $sortedLive = array();
        $sortedUpcoming = array();
        $sortedLive = Mage::getSingleton('adminhtml/session')->getData('categoryevent_sort_live_queue');
        $sortedUpcoming = Mage::getSingleton('adminhtml/session')->getData('categoryevent_sort_upcoming_queue');
        return array('live' => $sortedLive, 'upcoming' => $sortedUpcoming);
    }
    
}