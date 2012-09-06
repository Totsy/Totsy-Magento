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
    const TOP_EVENT_CATEGORY_NAME = 'Top Events';

    // Every Events' parent category should be named as 'Events'
    const EVENT_CATEGORY_NAME = 'Events';

    // Identify the expired events' parent category, all expired events should under this category
    const EVENT_EXPIRED_CATEGORY_NAME = 'Expired Events';

    // Set up event collection date range
    const EVENT_CATEGORY_DATE_RANGE = 5;

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

    protected function _afterSaveCommit(){
        //Due to DB access, the following logic must stay here after commit, rather than _afterSave()
        Mage::helper('categoryevent/memcache')->getIndexDataObject(true); //Force rebuild/flush memcached result of today
        return parent::_afterSaveCommit();
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
                ->addAttributeToSelect('short_description')
                ->load()
                ->toArray();
            $liveNew = array();
            $upComingNew = array();

            //Get live and upcoming events base on default sorting logic
            foreach($eventArray as $event) {
                $eventId = $event['entity_id'];
                $starttimediff = strtotime( $event['event_start_date'] ) - strtotime( $sortDate ) - self::DEFAULT_REBUILD_LIFETIME;
                $endtimediff = strtotime( $event['event_end_date'] ) - strtotime( $sortDate );

                // fetch all products part of this category/event
                $category = Mage::getModel('catalog/category')->load($eventId);
                $products = $category->getProductCollection()
                    ->addAttributeToSelect('departments')
                    ->addAttributeToSelect('ages')
                    ->addAttributeToSelect('price')
                    ->addAttributeToSelect('special_price');

                $event['department'] = array();
                $event['age'] = array();
                $event['department_label'] = array();
                $event['age_label'] = array();
                $event['max_discount_pct'] = 0;

                // populate event metadata (classifications) and calculate the
                // maximum discount percentage by finding the highest discount
                // percentage across all products
                foreach ($products as $product) {
                    $departments = $product->getAttributeTextByStore('departments', Harapartners_Service_Helper_Data::TOTSY_STORE_ID);
                    $ages = $product->getAttributeTextByStore('ages', Harapartners_Service_Helper_Data::TOTSY_STORE_ID);

                    if (is_array($departments)) {
                        $event['department'] = $event['department'] + $departments;
                    } else if (is_string($departments)) {
                        $event['department'][] = $departments;
                    }

                    if (is_array($ages)) {
                        $event['age'] = $event['age'] + $ages;
                    } else if (is_string($ages)) {
                        $event['age'][] = $ages;
                    }

                    // store user-friendly labels also
                    $departments = $product->getAttributeTextByStore('departments', 0);
                    $ages = $product->getAttributeTextByStore('ages', 0);

                    if (is_array($departments)) {
                        $event['department_label'] = $event['department_label'] + $departments;
                    } else if (is_string($departments)) {
                        $event['department_label'][] = $departments;
                    }

                    if (is_array($ages)) {
                        $event['age_label'] = $event['age_label'] + $ages;
                    } else if (is_string($ages)) {
                        $event['age_label'][] = $ages;
                    }

                    // calculate discount percentage for the event
                    $priceDiff = $product->getPrice() - $product->getSpecialPrice();
                    if ($product->getPrice()) {
                        $discount = round($priceDiff / $product->getPrice() * 100);
                        $event['max_discount_pct'] = max(
                            $event['max_discount_pct'],
                            $discount
                        );
                    }
                }

                $event['department'] = array_values(
                    array_unique($event['department'])
                );
                $event['age'] = array_values(
                    array_unique($event['age'])
                );

                $event['department_label'] = array_values(
                    array_unique($event['department_label'])
                );
                $event['age_label'] = array_values(
                    array_unique($event['age_label'])
                );

                Mage::helper('categoryevent/sortentry')
                    ->getCategories($event,'department');

                Mage::helper('categoryevent/sortentry')
                    ->getCategories($event,'age');

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

                if(!$liveOriginal){
                    $liveOriginal = array();
                }

                if(!$upComingOriginal){
                    $upComingOriginal = array();
                }

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
        return date("Y-m-d H:i:s", (strtotime($sortDate)+self::DEFAULT_REBUILD_LIFETIME * self::EVENT_CATEGORY_DATE_RANGE));
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
                ->addAttributeToSelect(array('name', 'description', 'image', 'small_image', 'thumbnail', 'logo', 'event_start_date', 'event_end_date', 'is_active', 'url_path', 'url_key'))
                ->addFieldToFilter('parent_id', $parentCategoryId)
                ->addFieldToFilter('level', self::CATEGOTYEVENT_LEVEL)
                ->addFieldToFilter('is_active', '1')
                ->addFieldToFilter('event_start_date', array( "lt" => $endDate ))
                ->addFieldToFilter('event_end_date', array( "gt" => $startDate ))
                ->addAttributeToSort('event_start_date', 'desc');

        return $collection;
    }

    public function getExpCategoryCollection($parentCategoryId, $currentDate){
        $collection = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect(array('name', 'event_end_date', 'is_active'))
                ->addFieldToFilter('parent_id', $parentCategoryId)
                ->addFieldToFilter('level', self::CATEGOTYEVENT_LEVEL)
                ->addFieldToFilter('event_end_date', array( "lt" => $currentDate ));

        return $collection;
    }    

    //This is an external function to be used in Controller
    public function rebuildSortCollection($sortDate, $storeId){	
        return $this->loadByDate($sortDate, $storeId, true);
    }

    // ===== Cronjob related ===== //
    public function rebuildSortCron($schedule){
        $defaultTimezone = date_default_timezone_get();
        $mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
        date_default_timezone_set($mageTimezone);
        $sortDate = now("Y-m-d");
        date_default_timezone_set($defaultTimezone);
        $storeId = Mage_Core_Model_App::DISTRO_STORE_ID; //Harapartners, Yang: for now only rebuild totsy store
        return $this->rebuildSortCollection($sortDate, $storeId);
    }

    public function cleanUpExpiredEvents($schedule){
        return $this->cleanExpiredEvents();
        //return $this->cleanExpiredEvents(); // To revert changes in case of moving active event to expired category
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

    //For move expired categories/events to a certain category
    public function cleanExpiredEvents( $revert = false ){
        //Get current clean time
        $defaultTimezone = date_default_timezone_get();
        $mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
        date_default_timezone_set($mageTimezone);
        $currentDate = date("Y-m-d H:i:s");
        date_default_timezone_set($defaultTimezone);
        $endDate = $this->calculateEndDate($currentDate);
        //Get store Id
        $storeId = Mage_Core_Model_App::DISTRO_STORE_ID; //Harapartners, Yang: for now only affect on totsy store

        //Get source category and target category
        $eventParentCat = $this->getParentCategory(self::EVENT_CATEGORY_NAME, $storeId);
        $expiredParentCat = $this->getParentCategory(self::EVENT_EXPIRED_CATEGORY_NAME, $storeId);

        if(!!$eventParentCat
            && !!$eventParentCat->getId()
            && !!$expiredParentCat
            && !!$expiredParentCat->getId()){

                $parentCategoryId = $eventParentCat->getId();
                $expiredParentId = $expiredParentCat->getId();
                
                try {
                    if (!$revert){
                        Mage::log('In if block', null, 'ExpireEventCleanUp.log');
                        $expCollection = $this->getExpCategoryCollection($parentCategoryId, $currentDate)->load();
                        foreach ( $expCollection as $cat ){
                            $cat->move($expiredParentId, null);
                        }
                    }else {
                        Mage::log('In else block', null, 'ExpireEventCleanUp.log');
                        $collection = $this->getCategoryCollection($expiredParentId, $currentDate, $endDate)->load();
                        foreach ( $collection as $cat ){
                            $cat->move($parentCategoryId, null);
                        }
                    }
                }catch ( Exception $e ){
                    Mage::logException($e);
                    return null;
                }
        }

        $this->rebuildSortCollection($currentDate, $storeId);
        Mage::app()->getCacheInstance()->flush();
        Mage::app()->cleanCache();

        return $this;
    }
    
    /*
     * This function is for moving a single category that was in the 
     * expired parent category, back to the event or main category
    **/
    public function moveSingleCategoryFromExpiredToEvent($categoryId) {
		//Get current clean time
        $defaultTimezone = date_default_timezone_get();
        $mageTimezone = Mage::getStoreConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
        date_default_timezone_set($mageTimezone);
        $currentDate = date("Y-m-d H:i:s");
        date_default_timezone_set($defaultTimezone);
        //Get store Id
        $storeId = Mage_Core_Model_App::DISTRO_STORE_ID; //Harapartners, Yang: for now only affect on totsy store

        //Get source category and target category
        $eventParentCat = $this->getParentCategory(self::EVENT_CATEGORY_NAME, $storeId);
        $expiredParentCat = $this->getParentCategory(self::EVENT_EXPIRED_CATEGORY_NAME, $storeId);

        if($eventParentCat
            && $eventParentCat->getId()
            && $expiredParentCat
            && $expiredParentCat->getId()
            && $categoryId){

                $parentCategoryId = $eventParentCat->getId();
                $expiredParentId = $expiredParentCat->getId();
                try {
                        $collection = Mage::getModel('catalog/category')->load($categoryId);
                        $collection->move($parentCategoryId, null);
                }catch ( Exception $e ){
                    Mage::logException($e);
                    return null;
                }
        }

        $this->rebuildSortCollection($currentDate, $storeId);
        Mage::app()->getCacheInstance()->flush();
        Mage::app()->cleanCache();

        return $this;
	}
}
