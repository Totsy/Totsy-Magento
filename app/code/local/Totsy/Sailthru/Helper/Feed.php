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
            $this->_timeIsAhead = false
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