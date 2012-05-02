<?php
/**
 * Harapartners
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Harapartners License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.Harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Harapartners.com so we can send you a copy immediately.
 *
 */

class Harapartners_Categoryevent_Model_Sortentry extends Mage_Core_Model_Abstract{
	
	// Only this level is considered category event
	const CATEGOTYEVENT_LEVEL = 3;
	
	// Every Top Events' parent category should be named as 'Top Event'
	const TOP_EVENT_CATEGORY_NAME = 'Top Event';
	
	// Every Events' parent category should be named as 'Events'
	const EVENT_CATEGORY_NAME = 'Events';
	
	// Set up event collection date range
	const EVENT_CATEGORY_DATE_RANG = '5';
	
	const DEFAULT_REBUILD_LIFETIME = 86400;
	
	const CRON_REBUILD_LIFETIME = 3600;
	
    protected function _construct(){
        $this->_init('categoryevent/sortentry');
    }
    
    protected function _beforeSave(){
    	if(!$this->getId()){
    		$this->setData('created_at', now());
    	}
    	$this->setData('updated_at', now());
    	if(!$this->getStoreId()){
    		$this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
    	}
    	parent::_beforeSave();  
    }
    
    public function loadOneByAttribute($attrName, $attrValue, $storeId = null) {
    	$this->addData($this->getResource()->loadByAttribute($attrName, $attrValue, $storeId));
    	return $this;
    }
     
    public function loadLatestRecord($attrName, $attrValue, $storeId = null){
    	$this->addData($this->getResource()->loadLatestRecord($attrName, $attrValue, $storeId));
    	return $this;
    }
    
    //This is an external function to be used in Controller
	public function loadByDate($sortDate, $storeId, $forceRebuild = false){
    	try {
	    	$this->loadOneByAttribute('date', $sortDate, $storeId); 	
	    	if($forceRebuild || !$this->hasData()){ 		
				$this->_rebuildEntry($sortDate, $storeId, $forceRebuild);
	    	}
    	}catch (Exception $e){
			return null;  		
    	}
    	return $this;
    }
    
    public function filterByCurrentTime($sortDate, $currentTime, $storeId ) {
    	//Only use for front end to rebuild sort event base on current time
    	$live = json_decode($this->loadByDate($sortDate, $storeId, false)->getData('live_queue'), true);
		$upcoming = json_decode($this->loadByDate($sortDate, $storeId, false)->getData('upcoming_queue'), true);
		
		foreach ( $live as &$event ){
			if (strtotime($event['event_start_date']) - strtotime($currentTime) > 0){
				array_push( $upcoming, $event );
				unset( $live[array_search( $event, $live )] );
			}
		}
		
		foreach ( $upcoming as &$event ){
			if (strtotime($event['event_start_date']) - strtotime($currentTime) < 0){
				array_push( $live, $event );
				unset( $upcoming[array_search( $event, $upcoming )] );
			}			
		}
		
		$this->setData('live_queue', json_encode($live));
    	$this->setData('upcoming_queue', json_encode($upcoming));
    	$this->save();   	
    	return $this;	
    }
    
    protected function _rebuildEntry( $sortDate, $storeId, $forceRebuild = false ){
    	$eventParentCategory = $this->getParentCategory(self::EVENT_CATEGORY_NAME, $storeId);
    	$topEventParentCategory = $this->getParentCategory(self::TOP_EVENT_CATEGORY_NAME, $storeId);
    	$startDate = $sortDate;
    	$endDate = $this->calculateEndDate($sortDate);
    	
    	//Top events are always on top with default sort order
    	$topEventArray = array();
    	if(!!$topEventParentCategory && !!$topEventParentCategory->getId()){
    		$topEventArray = $this->getCategoryCollection($topEventParentCategory->getId(), $startDate, $endDate)
    				->load()
    				->toArray();
    	}
    	
    	//Regular events may subject to additional sorting logic, also split into live vs. upcoming
    	$eventArray = array();
    	if(!!$eventParentCategory && !!$eventParentCategory->getId()){
    		$eventArray = $this->getCategoryCollection($eventParentCategory->getId(), $startDate, $endDate)
    				->load()
    				->toArray();
    		$liveNew = array();
    		$upComingNew = array();
    		
    		//Get live and upcoming events base on default sorting logic
   	    	foreach( $eventArray as $event ){
	        	$eventId = $event['entity_id'];
	    		$starttimediff = strtotime( $event['event_start_date'] ) - strtotime( $sortDate ) - self::DEFAULT_REBUILD_LIFETIME;
	    		$endtimediff = strtotime( $event['event_end_date'] ) - strtotime( $sortDate );
	    			
	    		if ( ($starttimediff <= 0) && ($endtimediff > 0) ) {	    			
	    			array_push( $liveNew, $event );
	    		}elseif ( ($starttimediff > 0) && ($endtimediff > 0) ) {	    			
	    			array_push( $upComingNew, $event );
	    		}
	    	}
	    	
    		if ($forceRebuild){
				$latestRecord = $this; 		
    		}else {
    			$latestRecord = Mage::getModel('categoryevent/sortentry')->loadLatestRecord('date', $sortDate, $storeId);
    		}
    		
    		//Update live and upcoming sort base on customerize logic which is drag and drop result for totsy
	    	if ( $latestRecord->hasData() ) {
	    		$liveOriginal = json_decode($latestRecord->getData('live_queue'), true);
	    		$upComingOriginal = json_decode($latestRecord->getData('upcoming_queue'), true);
	    		
			    //update live queue
			   	foreach ( $liveOriginal as $live ){
			   		if ( in_array($live, $liveNew) ){
						unset( $liveNew[array_search( $live, $liveNew )] );
			   		}else {
			   			unset( $liveOriginal[array_search( $live, $liveOriginal )] );
			   		}
			   	}
			   	foreach ( $liveNew as $new ){
			   		array_push( $liveOriginal, $new );
			   	}
			   	
			   	//update upcoming queue
			    foreach ( $upComingOriginal as $up ){
			   		if ( in_array($up, $upComingNew) ){
			   			unset( $upComingNew[array_search( $up, $upComingNew )] );
			   		}else {
			   			unset( $upComingOriginal[array_search( $up, $upComingOriginal )] );
			   		}
			   	}	
			    foreach ( $upComingNew as $new ){
			   		array_push( $upComingOriginal, $new );
			   	}
			   	
			   	$eventLiveSortedArray = $liveOriginal;
	    		$eventUpcomingSortedArray = $upComingOriginal;
	    	}else {
			   	$eventLiveSortedArray = $liveNew;
	    		$eventUpcomingSortedArray = $upComingNew;
	    		$this->setData('id', NULL);
	    	}
    	}
    	//save
    	$this->setData('date', $sortDate);
    	$this->setData('store_id', $storeId);
    	$this->setData('top_live_queue', json_encode($topEventArray));
    	$this->setData('live_queue', json_encode($eventLiveSortedArray));
    	$this->setData('upcoming_queue', json_encode($eventUpcomingSortedArray));
    	$this->save();
    	
    	return $this;
    }
    
    public function calculateEndDate($sortDate){
    	return date("Y-m-d H:i:s", (strtotime($sortDate)+self::DEFAULT_REBUILD_LIFETIME * self::EVENT_CATEGORY_DATE_RANG));
    }
    
    public function getParentCategory($categoryName, $storeId){
		$store = Mage::app()->getStore($storeId);
		if (!$store->getId()) {
			$store = Mage::app()->getStore(Mage_Core_Model_App::DISTRO_STORE_ID);
		}
		$storeCategoryCollection = Mage::getModel('catalog/category')->getCollection();
	    $storeCategoryCollection->addAttributeToSelect('name')
	    		->addFieldToFilter('parent_id', $store->getRootCategoryId())
	    		->addFieldToFilter('name', $categoryName);
	    foreach ($storeCategoryCollection as $collection){
	    	return $collection;
	    	break;
	    }
    }
    
    public function getCategoryCollection($parentCategoryId, $startDate, $endDate){
		//optimized date comparison logic, please do NOT touch!
	    $collection = Mage::getModel('catalog/category')->getCollection()
	       		->addAttributeToSelect(array('name','description', 'thumbnail','event_start_date', 'event_end_date', 'is_active', 'url_path', 'url_key', 'image'))
	       		->addFieldToFilter('parent_id', $parentCategoryId)
	       		->addFieldToFilter('level', self::CATEGOTYEVENT_LEVEL)
	       		->addFieldToFilter('is_active', '1')
				->addFieldToFilter('event_start_date', array( "lt" => $endDate ))
				->addFieldToFilter('event_end_date', array( "gt" => $startDate ))
				->addAttributeToSort('event_start_date', 'desc');
				
		return $collection;
    }
	
    //This is an external function to be used in Controller
    public function rebuildSortCollection($sortDate, $storeId){	
    	return $this->loadByDate($sortDate, $storeId, true);
    }
    
    // ===== Cronjob related ===== //
    public function rebuildSortCorn($schedule){
		$sortDate = now();
		$storeId = Mage_Core_Model_App::DISTRO_STORE_ID; //Harapartners, Yang: for now only rebuild totsy store
		return $this->rebuildSortCollection($sortDate, $storeId);
    }
    
    //This is an external function to be used in Controller
	public function saveUpdateSortCollection($liveSortedIdArray, $upComingSortedIdArray, $sortentry){
		$arrayLive = json_decode($sortentry->getData('live_queue'), true);
		$arrayUpcoming = json_decode($sortentry->getData('upcoming_queue'), true);
		$arrayLiveTemp = array();
		$arrayUpcomingTemp = array();
		//update original event array by dragging and dropping result
		if (!!$liveSortedIdArray && !empty($liveSortedIdArray)){
			foreach ($liveSortedIdArray as $liveId){
				foreach ($arrayLive as $live){
					if($live['entity_id'] == $liveId){
						array_push($arrayLiveTemp, $live);
						unset($live);
						break;
					}
				}
			}
			$sortentry->setData('live_queue',json_encode($arrayLiveTemp));				
		}
		if (!!$upComingSortedIdArray && !empty($upComingSortedIdArray)){
			foreach ($upComingSortedIdArray as $upId){
				foreach ($arrayUpcoming as $up){
					if($up['entity_id'] == $upId){
						array_push($arrayUpcomingTemp, $up);
						unset($up);
						break;
					}
				}
			}
			$sortentry->setData('upcoming_queue',json_encode($arrayUpcomingTemp));		
		}
		$sortentry->save();	
    	return $sortentry;
	}
}