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

/**
 * @method array getLiveQueue()
 * @method array getUpcomingQueue()
 */
class Harapartners_Categoryevent_Model_Sortentry
    extends Mage_Core_Model_Abstract
{
    protected $_cacheTag = 'categoryevent_sortentry';

    // Only this level is considered category event
    const CATEGORYEVENT_LEVEL = 3;

    // Every Events' parent category should be named as 'Events'
    const EVENT_CATEGORY_NAME = 'Events';
    const TOP_EVENT_CATEGORY_NAME = 'Top Events';

    // Identify the expired events' parent category, all expired events should under this category
    const EVENT_EXPIRED_CATEGORY_NAME = 'Expired Events';

    // Set up event collection date range
    const EVENT_CATEGORY_DATE_RANGE = 5;

    const DEFAULT_REBUILD_LIFETIME = 86400;

    const CRON_REBUILD_LIFETIME = 3600;

    protected function _construct()
    {
        $this->_init('categoryevent/sortentry');
    }

    protected function _beforeSave()
    {
        if (!$this->getId()) {
            $this->setData('created_at', now());
        }

        $this->setData('updated_at', now());

        if (!$this->getStoreId()) {
            $this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
        }

        parent::_beforeSave();
    }

    protected function _afterSave()
    {
        Mage::app()->getCache()->save(
            serialize($this->getData()),
            $this->_getCacheKey(),
            array($this->_cacheTag)
        );

        return parent::_afterSave();
    }

    public function loadCurrent($storeId = Mage_Core_Model_App::ADMIN_STORE_ID)
    {
        return $this->loadByDate(Mage::getModel('core/date')->date('Y-m-d'), $storeId, true);
    }

    public function loadByDate($date, $storeId = Mage_Core_Model_App::ADMIN_STORE_ID, $useRecent = false) {
        if (!$storeId) {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }

        $this->setData('store_id', $storeId);
        $this->setData('date', $date);

        $cache = Mage::app()->getCache();
        if ($cache->test($this->_getCacheKey())) {
            $this->addData(unserialize($cache->load($this->_getCacheKey())));
        } else {
            $this->getResource()->loadByDate($this, $date, $useRecent);
            if ($this->getId()) {
                Mage::app()->getCache()->save(
                    serialize($this->getData()),
                    $this->_getCacheKey(),
                    array($this->_cacheTag)
                );
            }
        }

        // ensure that a record was loaded
        if (!$this->getId()) {
            $this->setDate($date)->setStoreId($storeId)->rebuild();
        }

        return $this;
    }

    public function rebuild()
    {
        $date    = $this->getDate();
        $storeId = $this->getStoreId();

        // first look for an earlier sortentry
        $recentSortentry = Mage::getModel('categoryevent/sortentry')
            ->getCollection()
            ->addFieldToFilter('date', array('to' => $date, 'date' => true))
            ->addOrder('date', 'DESC')
            ->getFirstItem();

        $startDate = date('Y-m-d', strtotime($date));
        $endDate   = $this->calculateEndDate($date);

        $live = array();
        $upcoming = array();
        $copiedEventIds = array();
        if ($recentSortentry && $recentSortentry->getId()) {
            // copy all live events from the most recent sortentry
            $recentLive = json_decode($recentSortentry->getLiveQueue(), true);
            foreach ($recentLive as $event) {
                if ($preparedEvent = $this->_prepareEvent($event['entity_id'])) {
                    $live[] = $preparedEvent;
                    $copiedEventIds[] = $event['entity_id'];
                }
            }
        }

        $eventParentCategory = $this->getParentCategory(self::EVENT_CATEGORY_NAME, $storeId);
        if (!$eventParentCategory) {
            return $this;
        }

        $newEvents = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToFilter('parent_id', $eventParentCategory->getId())
            ->addAttributeToFilter('level', self::CATEGORYEVENT_LEVEL)
            ->addAttributeToFilter('is_active', '1')
            ->addAttributeToFilter('event_start_date', array('to' => $endDate, 'date' => true ))
            ->addAttributeToFilter('event_end_date', array('from' => $startDate, 'date' => true ))
            ->addAttributeToSort('event_start_date', 'asc');

        if (!empty($copiedEventIds)) {
            $newEvents->addAttributeToFilter('entity_id', array('nin' => $copiedEventIds));
        }

        foreach($newEvents as $event) {
            if ($event = $this->_prepareEvent($event->getId())) {
                if (strtotime($event['event_start_date']) < strtotime($date) + self::DEFAULT_REBUILD_LIFETIME) {
                    array_unshift($live, $event);
                } else {
                    array_push($upcoming, $event);
                }
            }
        }

        $this->setData('date', $date)
            ->setData('live_queue', json_encode($live))
            ->setData('upcoming_queue', json_encode($upcoming));

        return $this;
    }

    public function calculateEndDate($sortDate){
        return date("Y-m-d", (strtotime($sortDate)+self::DEFAULT_REBUILD_LIFETIME * self::EVENT_CATEGORY_DATE_RANGE));
    }

    protected function _prepareEvent($categoryId)
    {
        $stores = Mage::app()->getStores(false, true);
        $defaultStore = $stores['default']->getId();

        // fetch all products part of this category/event
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $event    = $category->getData();
        $products = $category->getProductCollection()
            ->addAttributeToSelect('departments')
            ->addAttributeToSelect('ages')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('special_price');

        $startDate = strtotime($this->getDate());
        $endDate   = strtotime($this->calculateEndDate($this->getDate()));

        if (!$event['is_active'] ||
            strtotime($event['event_start_date']) > $endDate ||
            strtotime($event['event_end_date']) < $startDate
        ) {
            return false;
        }

        $event['department'] = array();
        $event['age'] = array();
        $event['department_label'] = array();
        $event['age_label'] = array();
        $event['max_discount_pct'] = 0;

        // populate event metadata (classifications) and calculate the
        // maximum discount percentage by finding the highest discount
        // percentage across all products
        foreach ($products as $product) {
            $departments = $product->getAttributeTextByStore('departments', $defaultStore);
            $ages = $product->getAttributeTextByStore('ages', $defaultStore);

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

        return $event;
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
                ->addFieldToFilter('level', self::CATEGORYEVENT_LEVEL)
                ->addFieldToFilter('is_active', '1')
                ->addFieldToFilter('event_start_date', array( "to" => $endDate, 'date' => true ))
                ->addFieldToFilter('event_end_date', array( "from" => $startDate, 'date' => true ))
                ->addAttributeToSort('event_start_date', 'asc');

        return $collection;
    }

    public function getExpCategoryCollection($parentCategoryId, $currentDate){
        $collection = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect(array('name', 'event_end_date', 'is_active'))
                ->addFieldToFilter('parent_id', $parentCategoryId)
                ->addFieldToFilter('level', self::CATEGORYEVENT_LEVEL)
                ->addFieldToFilter('event_end_date', array( "lt" => $currentDate ));

        return $collection;
    }    

    public function updateSortCollection(
        array $sortedLive = array(),
        array $sortedUpcoming = array()
    ) {
        $currentLive = json_decode($this->getData('live_queue'), true);
        $currentUpcoming = json_decode($this->getData('upcoming_queue'), true);

        $updatedLive = array();
        $updatedUpcoming = array();

        if (!empty($sortedLive)) {
            foreach ($currentLive as $event) {
                $idx = array_search($event['entity_id'], $sortedLive);
                $updatedLive[$idx] = $event;
            }
            ksort($updatedLive);
        } else {
            $updatedLive = $currentLive;
        }

        if (!empty($sortedUpcoming)) {
            foreach ($currentUpcoming as $event) {
                $idx = array_search($event['entity_id'], $sortedUpcoming);
                $updatedUpcoming[$idx] = $event;
            }
            ksort($updatedUpcoming);
        } else {
            $updatedUpcoming = $currentUpcoming;
        }

        $this->setData('live_queue', json_encode($updatedLive))
            ->setData('upcoming_queue', json_encode($updatedUpcoming));

        return $this;
    }

    /**
     * Adjust the queues (live & upcoming) to reflect the current date & time.
     *
     * @return Harapartners_Categoryevent_Model_Sortentry
     */
    public function adjustQueuesForCurrentTime()
    {
        $now = Mage::getModel('core/date')->timestamp();

        $live     = json_decode($this->getData('live_queue'), true);
        $upcoming = json_decode($this->getData('upcoming_queue'), true);

        foreach ($live as $idx => $event) {
            if (strtotime($event['event_start_date']) > $now) {
                // move this event to Upcoming
                array_unshift($upcoming, $event);
                unset($live[$idx]);
            }
        }

        foreach ($upcoming as $idx => $event) {
            if (strtotime($event['event_start_date']) < $now &&
                strtotime($event['event_end_date']) > $now
            ) {
                // move this event to Live
                array_unshift($live, $event);
                unset($upcoming[$idx]);
            }
        }

        $this->setData('live_queue', json_encode($live))
            ->setData('upcoming_queue', json_encode($upcoming));

        return $this;
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
                        $expCollection = $this->getExpCategoryCollection($parentCategoryId, $currentDate)->load();
                        foreach ( $expCollection as $cat ){
                            $cat->move($expiredParentId, null);
                        }
                    }else {
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

        //$this->rebuildSortCollection($currentDate, $storeId);
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

        //$this->rebuildSortCollection($currentDate, $storeId);
        Mage::app()->getCacheInstance()->flush();
        Mage::app()->cleanCache();

        return $this;
    }

    protected function _getCacheKey()
    {
        return $this->_cacheTag . '_' .
            $this->getStoreId() . '_' .
            date('Y-m-d', strtotime($this->getDate()));
    }
}
