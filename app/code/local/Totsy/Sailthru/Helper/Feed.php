<?php

/**
 * @category    Totsy
 * @package     Totsy_Sailthru
 * @author      Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Sailthru_Helper_Feed extends Mage_Core_Helper_Abstract
{

    private $_timeDiff = 0;
    // is default magento time is ahead of actual server time
    private $_timeIsAhead = false;
    private $_startDate = null;
    private $_startTime = null;
    private $_min_datetime = null;
    private $_max_datetime = null;
    private $_order = false; // true = DESC; false = ACS
    private $_excludeList = array();

    public function __call($name,$argiments){
        if (substr($name,0,3) == 'get'){
            $name = substr($name,3);
            $name = lcfirst($name);
            if (isset($this->{'_'.$name})){
                return $this->{'_'.$name};
            }
        }
    }

    /**
    * send NO CACHE json headers 
    *
    * @return void
    */
    public function sendHeaders()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');
    }

    /**
    * Process feed parameters
    *
    * @return void
    */
    public function processor()
    {
        $this->setMagentoTimeDiff();
        $this->_processOrder();
        $this->_processStartDate();
        $this->_processStartTime();
        $this->_processExclude();

        $this->_min_datetime = $this->_startDate;
        $this->_max_datetime = strtotime('+2 days',$this->_startDate);
    }

    public function setMagentoTimeDiff()
    {
        $defaultTimezone = date_default_timezone_get();
        $mageTimezone = Mage::getStoreConfig(
            Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE
        );
        date_default_timezone_set($mageTimezone);
        $time = time();
        date_default_timezone_set($defaultTimezone);
        $defaultTime = time();

        if ($defaultTime>$time) {
            $this->_timeDiff = $defaultTime - $time;
            $this->_timeIsAhead = false;
        } else {
            $this->_timeDiff = $time - $defaultTime;
            $this->_timeIsAhead = true;
        }

    } 

    public function timeMachine(&$time,$format=null){
        $time = strtotime($time);
        
        if ($this->_timeIsAhead){
            $time = $time - $this->_timeDiff;
        } else {
            $time = $time + $this->_timeDiff;
        }

        if (!is_null($format)){
            $time = date($format,$time);
        }
    }

    public function formatEvent(&$event){
        return array(
            'id'             => $event['entity_id'],
            'name'           => $event['name'],
            'url'            => Mage::getBaseUrl().$event['url_path'],
            'description'    => $event['description'],
            'short'          => $event['short_description'],
            'availableItems' => !empty($event['products'])?'YES':'NO',
            'image'          => $this->_getImage($event),
            'image_small'    => $this->_getImage($event,'small'),
            'discount'       => $event['max_discount_pct'],
            'start_date'     => $event['event_start_date'],
            'end_date'       => $event['event_end_date'],
            'categories'     => $event['department_label'],
            'ages'           => $event['age_label'],
            'items'          => $event['products'],
            'tags'           => implode(',',$event['age_label'])
        );

    }

    public function formatPCEvent(&$event,$type){
        $result = array(
            'name'           => $event['name'],
            'url'            => Mage::getBaseUrl().$event['url_path'],
            'start_date'     => $event['event_start_date'],
            'end_date'       => $event['event_end_date']
        );
        unset($result[$type.'_date']);
        return $result;
    }

    public function timeConverter($date,$plus=null){
        $dsec = strtotime($date);
        if (!is_null($plus)){
            $dsec = strtotime($plus,$dsec);
        }
        $dd = date('Y-m-d 00:00:00',$dsec);
        return strtotime($dd);
    }

    public function filter($events, $type='end'){
        $collector = array();
        
        if (empty($events) || !is_array($events)){
            return $collector; 
        }
        foreach($events as $event){
            $event_time = $this->timeConverter($event['event_'.$type.'_date']);

            if ($event_time>$this->_min_datetime 
                && $event_time<$this->_max_datetime ){
                $collector[] = $event;
            } 
        }

        return $collector;
    }

    public function goingLive($events){
        $collector = array();
        
        if (empty($events) || !is_array($events)){
            return $collector; 
        }
        foreach($events as $event){
            $event_time = $this->timeConverter($event['event_start_date']);

            if ($event_time>=$this->_min_datetime 
                && $event_time<$this->_max_datetime ){
                $collector[] = $event;
            } 
        }

        return $collector;
    }

    private function _getImage($event,$type=''){
        if (!empty($type)){
            $type.= '_';
        }

        $image = $type.'image';
        
        if (!empty($event[$image])){
            $image = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).
                            'catalog/category/'.
                            $event[$image];
        } else{
            $image = Mage::getBaseUrl().
                            'skin/frontend/enterprise/bootstrap/images/'.
                            'catalog/product/placeholder/image.jpg';
        }
        return $image;
    }

    private function _processStartDate()
    {
        $this->_startDate = strtotime(date('Y-m-d'));

        if (empty($_GET['start_date'])) { 
            return;
        }

        if ( preg_match('/[\d]{4}[\-][\d]{2}[\-][\d]{2}/i', $_GET['start_date'], $m)) {
            $this->_startDate = strtotime($m[0]);
        }
    }

    private function _processStartTime()
    {
        if (!empty($_GET['start_time']) 
            && preg_match('/[\w]{2}/', $_GET['start_time']) 
            && strtolower($_GET['start_time']) == 'am' 
        ) {
            $this->_startTime = '08:00:00';
        }

        if (!empty($_GET['start_time'])
            && preg_match('/[\d]{2}[\:][\d]{2}[\:][\d]{2}/', $_GET['start_time'])
        ) {
            $st = preg_replace('/[^\d\:]+/', '', $_GET['start_time']);
            if (strlen($st)==8) {
                $this->_startTime = $st;
                unset($st);
            }
            $this->timeMachine($this->_startTime,'H:i:s');
        }
    }

    private function _processOrder ()
    {
        
        if (empty($_GET['order'])) {
            return;
        }

        if (strtolower($_GET['order']) == 'desc') {
            $this->_order = true; //DESC
        }

    }

    private function _processExclude()
    {
        if (!empty($_GET['exclude']) && preg_match('/[\d\,]+/',$_GET['exclude'])) {
            $exclude_list = explode(',', $_GET['exclude']);
            foreach($exclude_list as $el){
                if (is_numeric($el)) {
                    $this->_excludeList[] = $el;
                }
            }
            unset($exclude_list);
        }
    }
}