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
 
class Harapartners_Categoryevent_Block_Adminhtml_Sort_Index extends Mage_Adminhtml_Block_Widget_Container {
    
	public function __construct(){
        parent::__construct();
        $this->setTemplate('categoryevent/sort/index.phtml');
    }

    public function isSingleStoreMode() {  	
        if (!Mage::app()->isSingleStoreMode()) {      	
        	return false;
        }
        return true;
    }
    
    public function getPostKey(){  	
    	if (!!(Mage::getSingleton('adminhtml/url')->getSecretKey("adminhtml_sort","post"))){   		
    		return Mage::getSingleton('adminhtml/url')->getSecretKey("adminhtml_sort","post");
    	}else {		
    		return false;
    	}
    }
    
    public function getEventListHtml(){ 	
    	return $this->getChildHtml('categoryevent_adminhtml_sort_edit');	
    }
    
    public function getSortPostResponse( $var ){	
    	$sortPostResponse = '';   	
    	if ($var === 'id'){
    		
		    $sortPostResponse = $this->getRequest()->getParam('store');
			if (isset($sortPostResponse)){
				$sortPostResponse = '/store/'.$this->getRequest()->getParam('store').'/';
			}else {
				$sortPostResponse = '';
			}
    		
    	}elseif ($var === 'date') {
    		
		    $sortPostResponse = $this->getRequest()->getPost('sort_date');
			if (isset($sortPostResponse)){
				$sortPostResponse = $this->getRequest()->getPost('sort_date');
			}else {
				$sortPostResponse = date("Y-m-d");
			}
    	}
    	return $sortPostResponse;
    }
}